<?php
namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertCurrencyControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'dictionary.main.Title');
        $form->addText('symbol', 'Symbol');
        $form->addText('code', 'Köd');
        $form->addSubmit('send', 'dictionary.main.Save')->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    public function permissionValidated()
    {
        if ($this->presenter->template->member->users_roles->settings_edit === 0) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->redirect('this');
        }
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $exists = $this->database->table('currencies')->where('title = ? OR code = ? OR symbol = ?',
            $form->values->title, $form->values->code, $form->values->symbol);

        if ($exists->count() > 0) {
            $this->presenter->flashMessage('Měna, symbol nebo kód už je v seznamu', 'error');
            $this->presenter->redirect('this');
        } else {
            $this->database->table('currencies')->insert([
                'title' => $form->values->title,
                'code' => $form->values->code,
                'symbol' => $form->values->symbol
            ]);

            $this->presenter->redirect('this');
        }
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertCurrencyControl.latte');
        $this->template->render();
    }

}