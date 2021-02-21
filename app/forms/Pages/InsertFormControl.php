<?php

namespace Apps\Forms\Pages;

use App\Model\Document;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class InsertFormControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
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

    /**
     * Create nwe page
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $doc = new Document($this->database);
        $doc->setForm($form->getValues());

        // Set parent page for every page type
        $pageType = $this->database->table('pages_types')->get($form->values->section);

        if ($pageType) {
            $doc->setParent($pageType->pages_id);
        }

        $doc->setType($form->values->section);

        $page = $doc->create($this->presenter->user->getId());

        $this->presenter->redirect(':Admin:Pages:detail', ['id' => $page]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/InsertFormControl.latte');
        $template->render();
    }
}