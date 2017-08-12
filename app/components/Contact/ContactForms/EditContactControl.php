<?php

namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class EditContactControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;
    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit contact
     */
    function createComponentEditForm()
    {
        $this->template->id = $this->presenter->getParameter('id');

        $groups = $this->database->table("contacts_categories")->fetchPairs("id", "title");

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->addHidden('contact_id');
        $form->addHidden('pages_id');
        $form->addText("name");
        $form->addText("company");
        $form->addRadioList("type", "", array(0 => " osoby", 1 => " organizace"));
        $form->addText("tpost");
        $form->addText("email");
        $form->addText("phone");
        $form->addSelect("categories_id", "", $groups);
        $form->addText("street");
        $form->addText("zip");
        $form->addText("city");
        $form->addText("vatin");
        $form->addText("vatid");
        $form->addText("banking_account");
        $form->addText("dateofbirth");
        $form->addTextArea("notes");

        $page = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $contact = $this->database->table("contacts")->where("pages_id", $this->presenter->getParameter("id"))->fetch();

        $arr = array(
            "contact_id" => $contact->id,
            "pages_id" => $page->id,
            "name" => $contact->name,
            "company" => $contact->company,
            "post" => $contact->post,
            "type" => $contact->type,
            "email" => $contact->email,
            "phone" => $contact->phone,
            "categories_id" => $contact->categories_id,
            "street" => $contact->street,
            "zip" => $contact->zip,
            "city" => $contact->city,
            "banking_account" => $contact->banking_account,
            "vatin" => $contact->vatin,
            "vatid" => $contact->vatid,
            "notes" => $contact->notes,
            "dateofbirth" => $contact->date_of_birth,
        );

        $form->setDefaults($arr);

        $form->addSubmit("submitm");

        $form->onSuccess[] = [$this, "editFormSucceeded"];
        $form->onValidate[] = [$this, "editFormValidated"];
        return $form;
    }

    function editFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE && strlen($form->values->email) > 0) {
            $this->onSave($form->values->pages_id, $error = 1);
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
            ->where(array(
                "id" => $form->values->contact_id,
            ))
            ->update(array(
                "name" => $form->values->name,
                "company" => $form->values->company,
                "post" => $form->values->post,
                "type" => $form->values->type,
                "email" => $form->values->email,
                "phone" => $form->values->phone,
                "contacts_categories_id" => $form->values->categories_id,
                "street" => $form->values->street,
                "zip" => $form->values->zip,
                "city" => $form->values->city,
                "vatin" => $form->values->vatin,
                "vatid" => $form->values->vatid,
                "banking_account" => $form->values->banking_account,
                "date_of_birth" => $form->values->dateofbirth,
                "notes" => $form->values->notes,
            ));

        $this->onSave($form->values->pages_id);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditContactControl.latte');

        $template->render();
    }

}

interface IEditContactControlFactory
{
    /** @return \Caloriscz\Contacts\ContactForms\EditContactControl */
    function create();
}