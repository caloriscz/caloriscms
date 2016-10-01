<?php
namespace Caloriscz\Settings\Currency;

use Nette\Application\UI\Control;

class InsertCurrencyControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("title", "dictionary.main.Title");
        $form->addText("symbol", "Symbol");
        $form->addText("code", "Köd");

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormSucceeded;
        $form->onValidate[] = $this->permissionValidated;
        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->presenter->template->member->users_roles->settings_edit == 0) {
            $this->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->redirect(this);
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $exists = $this->database->table("currencies")->where("title = ? OR code = ? OR symbol = ?",
            $form->values->title, $form->values->code, $form->values->symbol);

        if ($exists->count() > 0) {
            $this->presenter->flashMessage("Měna, symbol nebo kód už je v seznamu", "error");
            $this->presenter->redirect(this);
        } else {
            $this->database->table("currencies")->insert(array(
                "title" => $form->values->title,
                "code" => $form->values->code,
                "symbol" => $form->values->symbol,
            ));

            $this->presenter->redirect(this);
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertCurrencyControl.latte');

        $template->render();
    }

}