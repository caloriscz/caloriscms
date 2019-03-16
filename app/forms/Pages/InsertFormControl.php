<?php

namespace Apps\Forms\Pages;

use App\Model\Document;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertFormControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();

        if ($this->presenter->getParameter('type') === '') {
            $pageType = 9;
        } else {
            $pageType = $this->presenter->getParameter('type');
        }

        $form->addHidden('id');
        $form->addHidden('section');
        $form->addText('title');

        $form->setDefaults([
            'section' => $pageType,
        ]);

        $form->addSubmit('submit', 'Vytvořit');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $doc = new Document($this->database);
        $doc->setForm($form->getValues());
        $doc->setType($form->values->section);
        $page = $doc->create($this->presenter->user->getId());

        $this->presenter->redirect(':Admin:Pages:detail', ['id' => $page]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/InsertFormControl.latte');
        $template->render();
    }
}