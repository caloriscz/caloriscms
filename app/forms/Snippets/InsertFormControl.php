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

    /** @var EntityManager @inject */
    public $em;

    public function __construct(Context $database, EntityManager $em)
    {
        parent::__construct();
        $this->database = $database;
        $this->em = $em;
    }

    public function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id')->setAttribute('class', 'form-control');
        $form->addText('title', 'dictionary.main.Title');

        $form->setDefaults(['id' => $this->getPresenter()->getParameter('id')]);

        $form->addSubmit('submit', 'dictionary.main.Create')->setHtmlId('formxins');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];

        return $form;
    }

    public function permissionValidated()
    {
        if ($this->getPresenter()->template->member->users_roles->pages === 0) {
            $this->getPresenter()->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->getPresenter()->redirect('this');
        }
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
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