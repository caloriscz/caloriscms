<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class InsertContactForMemberControl extends Control
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
        $memberTable = $this->database->table("users")->get($this->presenter->getParameter("id"));

        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("user");
        $form->addHidden("page");
        $form->addSubmit("submitm", "dictionary.main.Create")
            ->setAttribute("class", "btn btn-success");
        $form->setDefaults(array(
            "page" => $this->presenter->getParameter("id"),
            "user" => $memberTable->id,
        ));

        $form->onSuccess[] = $this->insertFormSucceeded;
        $form->onValidate[] = $this->insertFormValidated;
        return $form;
    }

    function insertFormValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        if (!$this->presenter->template->member->users_roles->members_create) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->presenter->redirect(":Admin:Members:default", array("id" => null));
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType(5);
        $doc->createSlug("contact-" . $form->values->user);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->presenter->user->getId());

        \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page);

        $arr = array(
            "pages_id" => $page,
            "type" => 1,
            "name" => 'contact-' . $form->values->user,
            "users_id" => $form->values->user,
        );

        $this->database->table("contacts")
            ->insert($arr);

        $this->presenter->redirect(this, array("id" => $form->values->page));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertContactForMemberControl.latte');

        $template->render();
    }

}
