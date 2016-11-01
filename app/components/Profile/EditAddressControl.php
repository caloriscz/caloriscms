<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class EditAddressControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentEditAddressForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "dictionary.main.Name");
        $form->addText("street", "dictionary.main.Street");
        $form->addText("zip", "dictionary.main.ZIP");
        $form->addText("city", "dictionary.main.City");

        $address = $this->database->table("contacts")->get($this->presenter->getParameter("id"));

        $form->setDefaults(array(
            "id" => $address->id,
            "name" => $address->name,
            "street" => $address->street,
            "zip" => $address->zip,
            "city" => $address->city,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');
        $form->onSuccess[] = $this->editAddressFormSucceeded;

        return $form;
    }

    function editAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")->where(array(
            "id" => $form->values->contacts_id,
        ))->update(array(
            "name" => $form->values->name,
            "street" => $form->values->street,
            "zip" => $form->values->zip,
            "city" => $form->values->city,
        ));

        $this->presenter->redirect(":Front:Profile:address", array("id" => $form->values->id));
    }

       public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditAddressControl.latte');


        $template->render();
    }

}
