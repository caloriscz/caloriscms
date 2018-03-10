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

    protected function createComponentEditSettingsForm()
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('category_id');
        $form->addHidden('setkey');
        $form->addText('setvalue', 'dictionary.main.Description');

        $arr = array_filter(['category_id' => $this->presenter->getParameter('id')]);

        $form->setDefaults($arr);

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'editSettingsSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    public function permissionValidated(BootstrapUIForm $form)
    {
        if ($this->presenter->template->member->users_roles->settings === 0) {
            $this->presenter->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->presenter->redirect('this');
        }
    }

    public function editSettingsSucceeded(BootstrapUIForm $form)
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
                'settings_categories_id' => 10,
            ];
        } else {
            $arr = [
                'admin_editable' => 1,
                'settings_categories_id' => $this->presenter->getParameter('id'),
            ];
        }

        $this->template->database = $this->database;

        $template->settingsDb = $this->database->table('settings')
            ->where($arr);
        $template->setFile(__DIR__ . '/EditSettingsControl.latte');

        $template->render();
    }

}