<?php
namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class InsertContactControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("pages_id", $this->getParameter("id"));
        $form->addRadioList("type", "Osoba nebo organizace", array(0 => " osoby", 1 => " organizace"));
        $form->addText("title", "dictionary.main.Title")
            ->setRequired($this->presenter->translator->translate('messages.pages.NameThePage'));

        $form->setDefaults(array(
            "type" => 0
        ));

        $form->addSubmit("submitm", "dictionary.main.CreateNewContact")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $arr = array(
            "users_id" => null,
            "type" => $form->values->type,
        );

        if ($form->values->type == 0) {
            $arr["name"] = $form->values->title;
        } else {
            $arr["company"] = $form->values->title;
        }

        $id = $this->database->table("contacts")->insert($arr);

        $this->presenter->redirect(":Admin:Contacts:detail", array("id" => $id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertContactControl.latte');

        $template->render();
    }

}
