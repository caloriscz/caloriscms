<?php
namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertCountryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('country_cs', 'Země (česky)');
        $form->addText('country_en', 'Země (anglicky)');

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    public function permissionValidated()
    {
        if ($this->presenter->template->member->users_roles->settings_edit == 0) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->redirect('this');
        }
    }


    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $exists = $this->database->table('countries')->where('title_cs = ? OR title_en = ?',
            $form->values->country_cs, $form->values->country_en);

        if ($exists->count() > 0) {
            $this->flashMessage('Země už je v seznamu', 'error');
            $this->redirect(this);
        } else {
            $this->database->table('countries')->insert(array(
                'title_cs' => $form->values->country_cs,
                'title_en' => $form->values->country_en,
            ));

            $this->redirect('this');
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertCountryControl.latte');

        $template->render();
    }

}