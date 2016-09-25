<?php
namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class InsertCommunicationControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert communication
     */
    function createComponentInsertForm()
    {
        $types = array(
            "E-mail" => "E-mail", "Telefon, domácí" => "Telefon, domácí",
            "Telefon, pracovní" => "Telefon, pracovní", "Fax" => "Fax",
            "Webová adresa" => "Webová adresa", "Skype" => "Skype"
        );

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("id");
        $form->addSelect('type', 'Typ komunikace', $types)
            ->setAttribute('class', 'form-control');
        $form->addText('type_value', 'Hodnota');
        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->setDefaults(array(
            "id" => $this->presenter->getParameter('id'),
        ));

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts_communication")
            ->insert(array(
                "contacts_id" => $form->values->id,
                "communication_type" => $form->values->type,
                "communication_value" => $form->values->type_value,
            ));

        $this->presenter->redirect(this, array("id" => $form->values->id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertCommunicationControl.latte');

        $template->render();
    }

}
