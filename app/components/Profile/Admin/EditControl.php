<?php
namespace Caloriscz\Profile\Admin;

use Nette\Application\UI\Control;

class EditControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit by user
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $members = $this->database->table("users")
            ->get($this->presenter->user->getId());

        $cols = array(
            "username" => $members->username,
            "email" => $members->email,
            "name" => $members->name,
        );

        $form->addGroup("Základní nastavení");
        $form->addText("name", "Name");

        $form->setDefaults(array(
            "name" => $cols["name"],
        ));

        $form->addSubmit("submit", "dictionary.main.Save");
        $form->onSuccess[] = array($this, 'editFormSucceeded');
        return $form;
    }

    /**
     * Edit user settings by user
     * @return array Redirect parameters
     */
    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->get($this->presenter->user->getId())
            ->update(array(
                "name" => $form->values->name,
            ));

        $this->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditControl.latte');

        $template->render();
    }

}