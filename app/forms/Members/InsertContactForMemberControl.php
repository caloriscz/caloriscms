<?php

namespace App\Forms\Members;

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
     * @return BootstrapUIForm
     */
    public function createComponentInsertForm(): BootstrapUIForm
    {
        $memberTable = $this->database->table('users')->get($this->presenter->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->role = 'form';
        $form->addHidden('user');
        $form->addHidden('page');
        $form->addSubmit('submitm', 'dictionary.main.Create')
            ->setAttribute('class', 'btn btn-success btn-sm');
        $form->setDefaults([
            'page' => $this->presenter->getParameter('id'),
            'user' => $memberTable->id,
        ]);

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'insertFormValidated'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormValidated(BootstrapUIForm $form): void
    {
        if (!$this->presenter->template->member->users_roles->members) {
            $this->presenter->flashMessage($this->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->presenter->redirect(':Admin:Members:default', ['id' => null]);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $arr = [
            'pages_id' => $page,
            'type' => 1,
            'name' => 'contact-' . $form->values->user,
            'users_id' => $form->values->user,
        ];

        $this->database->table('contacts')
            ->insert($arr);

        $this->presenter->redirect(this, ['id' => $form->values->page]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertContactForMemberControl.latte');
        $this->template->render();
    }

}
