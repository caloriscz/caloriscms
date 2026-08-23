<?php
namespace App\Forms\Settings;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditSettingsControl extends Control
{

    public Explorer $database;
    private ?string $keyPrefix;

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

        foreach ($values['set'] as $key => $value) {
            $this->database->table('settings')->where(['setkey' => $key])->update(['setvalue' => $value]);
        }

        $this->presenter->redirect('this', ['id' => $form->values->category_id]);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->langSelected = $this->presenter->translator->getLocale();

        $arr = ['admin_editable' => 1];

        $this->template->database = $this->database;

        $settingsDb = $this->database->table('settings')->where($arr);

        if ($this->keyPrefix !== null) {
            $settingsDb->where('setkey LIKE ?', $this->keyPrefix . '%');
        }

        $template->settingsDb = $settingsDb->order('id');
        $template->setFile(__DIR__ . '/EditSettingsControl.latte');
        $template->render();
    }

}
