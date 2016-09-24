<?php
namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class EditContactControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

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

        $categories = new \App\Model\Category($this->database);
        $cats = $categories->getSubIds($this->template->settings['categories:id:contact']);
        $groups = $this->database->table("categories")
            ->where("id", $cats)->fetchPairs("id", "title");

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addGroup('');
        $form->addHidden('contact_id');
        $form->addHidden('pages_id');
        $form->addText("name", "dictionary.main.Name")
            ->setAttribute("placeholder", "dictionary.main.Name");
        $form->addText("company", "dictionary.main.Company")
            ->setAttribute("placeholder", "dictionary.main.Company");
        $form->addRadioList("type", "Osoba nebo organizace", array(0 => " osoby", 1 => " organizace"));
        $form->addText("post", "dictionary.main.Post")
            ->setAttribute("placeholder", "dictionary.main.Post")
            ->setOption("description", 1);
        $form->addText("email", "E-mail")
            ->setAttribute("placeholder", "dictionary.main.Email")
            ->setAttribute("class", "form-control");
        $form->addText("phone", "dictionary.main.Phone")
            ->setAttribute("placeholder", "dictionary.main.Phone")
            ->setAttribute("class", "form-control");
        $form->addSelect("categories_id", "dictionary.main.Category", $groups)
            ->setAttribute("class", "form-control");

        $form->addGroup('dictionary.main.Address');
        $form->addText("street", "Ulice")
            ->setAttribute("placeholder", "dictionary.main.Street")
            ->setOption("description", 1);
        $form->addText("zip", "dictionary.main.ZIP")
            ->setAttribute("placeholder", "dictionary.main.ZIP")
            ->setOption("description", 1);
        $form->addText("city", "Město")
            ->setAttribute("placeholder", "Město")
            ->setOption("description", 1);
        $form->addGroup('Firemní údaje');
        $form->addText("vatin", "IČ")
            ->setAttribute("placeholder", "dictionary.main.VatIn")
            ->setOption("description", 1);
        $form->addText("vatid", "DIČ")
            ->setAttribute("placeholder", "dictionary.main.VatId")
            ->setHtmlId("kurzy_ico")
            ->setOption("description", 1);
        $form->addText("banking_account", "Bankovní účet")
            ->setAttribute("placeholder", "Bankovní účet")
            ->setOption("description", 1);
        $form->addGroup('Ostatní');
        $form->addTextArea("notes", "dictionary.main.Notes")
            ->setAttribute("class", "form-control");


        $form->setDefaults(array(
            "contact_id" => $this->presenter->template->contact->id,
            "pages_id" => $this->presenter->template->contact->pages_id,
            "name" => $this->presenter->template->contact->name,
            "company" => $this->presenter->template->contact->company,
            "post" => $this->presenter->template->contact->post,
            "type" => $this->presenter->template->contact->type,
            "email" => $this->presenter->template->contact->email,
            "phone" => $this->presenter->template->contact->phone,
            "categories_id" => $this->presenter->template->contact->categories_id,
            "street" => $this->presenter->template->contact->street,
            "zip" => $this->presenter->template->contact->zip,
            "city" => $this->presenter->template->contact->city,
            "banking_account" => $this->presenter->template->contact->banking_account,
            "vatin" => $this->presenter->template->contact->vatin,
            "vatid" => $this->presenter->template->contact->vatid,
            "notes" => $this->presenter->template->contact->notes,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->onValidate[] = $this->editFormValidated;
        return $form;
    }

    function editFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE && strlen($form->values->email) > 0) {
            $this->presenter->flashMessage($this->translator->translate('messages.sign.fillInEmail'), "error");
            $this->presenter->redirect(":Admin:Contacts:detail", array("id" => NULL));
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
                "categories_id" => $form->values->categories_id,
                "street" => $form->values->street,
                "zip" => $form->values->zip,
                "city" => $form->values->city,
                "vatin" => $form->values->vatin,
                "vatid" => $form->values->vatid,
                "banking_account" => $form->values->banking_account,
                "notes" => $form->values->notes,
            ));

        $this->presenter->redirect(this, array("id" => $form->values->pages_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditContactControl.latte');

        $template->render();
    }

}
