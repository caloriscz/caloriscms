<?php
namespace App\Forms\Settings;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditSettingsControl extends Control
{

    /** settings.setvalue is varchar(120). */
    private const VALUE_LENGTH = 120;

    public Explorer $database;
    private ?string $keyPrefix;
    /** Values of a rejected submission, shown again instead of the stored ones. */
    private array $submitted = [];

    public function __construct(Explorer $database, ?string $keyPrefix = null)
    {
        $this->database = $database;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentEditSettingsForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('category_id');
        $form->addHidden('setkey');
        $form->addText('setvalue', 'Popisek');

        $arr = array_filter(['category_id' => $this->presenter->getParameter('id')]);

        $form->setDefaults($arr);

        $form->addSubmit('send', 'Uložit')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'editSettingsSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    /**
     * @throws AbortException
     */
    public function permissionValidated(): void
    {
        if (!$this->presenter->template->memberRole || $this->presenter->template->memberRole->settings === 0) {
            $this->presenter->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editSettingsSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        // Validate every value before writing any: purification can lengthen the
        // maintenance message past settings.setvalue, and a strict-mode overflow
        // or a 4-byte character used to fail with 500 after a partial save.
        $updates = [];
        foreach ($values['set'] as $key => $value) {
            if ($key === 'maintenance_message') {
                $value = \App\Security\HtmlContent::purify($value);
            }
            $label = function () use ($key): string {
                return $this->database->table('settings')->where(['setkey' => $key])->fetchField('description_cs') ?: $key;
            };
            if (mb_strlen($value, 'UTF-8') > self::VALUE_LENGTH) {
                $form->addError(sprintf('%s: hodnota má %d znaků (včetně HTML úprav), povoleno je nejvýše %d. Nic nebylo uloženo.',
                    $label(), mb_strlen($value, 'UTF-8'), self::VALUE_LENGTH));
            } elseif (preg_match('~[\x{10000}-\x{10FFFF}]~u', $value)) {
                $form->addError($label() . ': hodnota obsahuje nepodporované znaky (např. emoji). Nic nebylo uloženo.');
            }
            $updates[$key] = $value;
        }
        if ($form->hasErrors()) {
            $this->submitted = $values['set'];
            return;
        }

        $this->database->transaction(function () use ($updates): void {
            foreach ($updates as $key => $value) {
                $this->database->table('settings')->where(['setkey' => $key])->update(['setvalue' => $value]);
            }
        });

        $this->presenter->redirect('this', ['id' => $form->values->category_id]);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->langSelected = $this->presenter->translator->getLocale();

        $arr = ['admin_editable' => 1];

        $this->template->database = $this->database;
        $template->submitted = $this->submitted;

        $settingsDb = $this->database->table('settings')->where($arr);

        if ($this->keyPrefix !== null) {
            $settingsDb->where('setkey LIKE ?', $this->keyPrefix . '%');
        }

        $template->settingsDb = $settingsDb->order('id');
        $template->setFile(__DIR__ . '/EditSettingsControl.latte');
        $template->render();
    }

}
