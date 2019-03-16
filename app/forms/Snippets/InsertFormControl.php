<?php

namespace App\Forms\Snippets;

use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertFormControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    public function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id')->setAttribute('class', 'form-control');
        $form->addText('title', 'dictionary.main.Title');

        $form->setDefaults(['id' => $this->getPresenter()->getParameter('id')]);

        $form->addSubmit('submit', 'Vytvořit')->setHtmlId('formxins');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];

        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function permissionValidated(): void
    {
        if ($this->getPresenter()->template->member->users_roles->pages === 0) {
            $this->getPresenter()->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->getPresenter()->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('snippets')->insert([
            'keyword' => $form->values->title,
        ]);

        $this->getPresenter()->redirect('this');
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertFormControl.latte');
        $this->template->render();
    }

}