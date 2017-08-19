<?php

namespace Caloriscz\Profile\Admin;

use Nette\Application\UI\Control;
use Nette\Utils\Validators;

class EditControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;
    public $onSave;

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
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("email");
        $form->addText("name");
        $form->addCheckbox("adminbar")
            ->setDefaultValue($this->presenter->template->member->adminbar_enabled);
        $form->addSelect("language", "", array("cs" => "česky", "en" => "English"));

        $form->setDefaults(array(
            "name" => $this->presenter->template->member->name,
            "email" => $this->presenter->template->member->email,
            "language" => $this->presenter->request->getCookie('language_admin')
        ));

        $form->addSubmit("submit");

        $form->onSuccess[] = array($this, 'editFormSucceeded');
        $form->onValidate[] = array($this, 'validateFormSucceeded');
        return $form;
    }

    function validateFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (Validators::isEmail($form->values->email) == false) {
            $this->onSave("E-mail je povinný");
        }

        $userExists = $this->database->table("users")->where("email = ? AND NOT id = ?", $form->values->email, $this->presenter->user->getId());

        if ($userExists->count() > 0) {
            $this->onSave("E-mail již existuje");
        }
    }

    /**
     * Edit user settings by user
     * @return array Redirect parameters
     */
    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->get($this->presenter->user->getId())->update(array(
            "name" => $form->values->name,
            "email" => $form->values->email,
            "adminbar_enabled" => $form->values->adminbar,
        ));

        $this->presenter->response->setCookie('language_admin', $form->values->language, '180 days');
    }

    public function render()
    {
        $template = $this->template;
        $template->member = $this->presenter->template->member->username;
        $template->setFile(__DIR__ . '/EditControl.latte');

        $template->render();
    }
}

interface IEditControlFactory
{
    /** @return \Caloriscz\Profile\Admin\EditControl */
    function create();
}