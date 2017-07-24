<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class InsertAddressControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentInsertForm()
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

        $form->setDefaults(array(
            "contacts_group_id" => 2,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');
        $form->onSuccess[] = [$this, "insertFormSucceeded"];

        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType(5);
        $doc->createSlug("contact-" . $form->values->name);
        $doc->setTitle($form->values->name);
        $page = $doc->create($this->presenter->user->getId());

        // create new contact
        $this->database->table("contacts")->insert(array(
            "categories_id" => 5,
            "pages_id" => $page,
            "users_id" => $this->presenter->user->getId(),
            "name" => $form->values->name,
            "street" => $form->values->street,
            "zip" => $form->values->zip,
            "city" => $form->values->city,
        ));

        $this->presenter->redirect(":Front:Profile:addresses");
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertAddressControl.latte');


        $template->render();
    }

}
