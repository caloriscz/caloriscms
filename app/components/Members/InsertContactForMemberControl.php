<?php

namespace Caloriscz\Members;

use App\Model\Document;
use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertContactForMemberControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert contact
     */
    public function createComponentInsertForm()
    {
        $memberTable = $this->database->table('users')->get($this->presenter->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->role = 'form';
        $form->addHidden('user');
        $form->addHidden('page');
        $form->addSubmit('submitm', 'dictionary.main.Create')
            ->setAttribute('class', 'btn btn-success btn-sm');
        $form->setDefaults(array(
            'page' => $this->presenter->getParameter('id'),
            'user' => $memberTable->id,
        ));

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'insertFormValidated'];
        return $form;
    }

    public function insertFormValidated(BootstrapUIForm $form)
    {
        if (!$this->presenter->template->member->users_roles->members_create) {
            $this->presenter->flashMessage($this->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->presenter->redirect(':Admin:Members:default', array('id' => null));
        }
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $doc = new Document($this->database);
        $doc->setType(5);
        $doc->createSlug('contact-' . $form->values->user);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->presenter->user->getId());

        IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page);

        $arr = array(
            'pages_id' => $page,
            'type' => 1,
            'name' => 'contact-' . $form->values->user,
            'users_id' => $form->values->user,
        );

        $this->database->table('contacts')
            ->insert($arr);

        $this->presenter->redirect(this, array('id' => $form->values->page));
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertContactForMemberControl.latte');
        $this->template->render();
    }

}
