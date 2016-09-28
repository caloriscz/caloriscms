<?php
namespace Caloriscz\Page\Snippets;

use Nette\Application\UI\Control;

class InsertFormControl extends Control
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

        $form->addHidden("id")
            ->setAttribute("class", "form-control");
        $form->addText("title", "dictionary.main.Title");

        $form->setDefaults(array(
            "id" => $this->presenter->getParameter('id'),
        ));

        $form->addSubmit("submit", "dictionary.main.Create")
            ->setHtmlId('formxins');

        $form->onSuccess[] = $this->insertFormSucceeded;
        $form->onValidate[] = $this->permissionValidated;

        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->presenter->template->member->users_roles->pages_edit == 0) {
            $this->presenter->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("snippets")->insert(array(
            "keyword" => $form->values->title,
            "pages_id" => $form->values->id,
        ));

        $this->presenter->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertFormControl.latte');

        $template->render();
    }

}