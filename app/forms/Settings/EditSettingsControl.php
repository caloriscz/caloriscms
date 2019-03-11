<?php
namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditSettingsControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentEditSettingsForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

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
     * @param BootstrapUIForm $form
     */
    public function permissionValidated(BootstrapUIForm $form): void
    {
        if ($this->presenter->template->member->users_roles->settings === 0) {
            $this->presenter->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function editSettingsSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        foreach ($values['set'] as $key => $value) {
            $this->database->table('settings')->where([
                'setkey' => $key,
        ])
                ->update([
                    'setvalue' => $value,
                ]);
        }

        $this->presenter->redirect('this', ['id' => $form->values->category_id]);
    }

    public function render()
    {
        $template = $this->template;
        $template->langSelected = $this->presenter->translator->getLocale();

        if (!$this->presenter->getParameter('id')) {
            $arr = [
                'admin_editable' => 1,
            ];
        } else {
            $arr = [
                'admin_editable' => 1,
            ];
        }

        $this->template->database = $this->database;

        $template->settingsDb = $this->database->table('settings')
            ->where($arr);
        $template->setFile(__DIR__ . '/EditSettingsControl.latte');
        $template->render();
    }

}