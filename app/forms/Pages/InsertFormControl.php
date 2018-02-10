<?php

namespace Apps\Forms\Pages;

use App\Model\Document;
use App\Model\IO;
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
        $this->database = $database;
        $this->em = $em;
    }

    protected function createComponentInsertForm()
    {
        $category = $this->database->table('pages_categories');

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);

        if ($this->presenter->getParameter('type') === '') {
            $pageType = 9;
        } else {
            $pageType = $this->presenter->getParameter('type');
        }

        if ($category->count() > 1) {
            $form->addSelect('category', '', $category->fetchPairs('id', 'title'));
        } else {
            $form->addHidden('category');
        }

        $form->addHidden('id');
        $form->addHidden('section');
        $form->addText('title');

        $form->setDefaults([
            'section' => $pageType,
        ]);

        $form->addSubmit('submit', 'dictionary.main.Create');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $doc = new Document($this->database);
        $doc->setCategory($form->values->category);
        $doc->setType($form->values->section);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->presenter->user->getId());

        IO::directoryMake(APP_DIR . '/media/' . $page, 0755);
        IO::directoryMake(APP_DIR . '/media/' . $page . '/tn', 0755);

        $this->presenter->redirect(':Admin:Pages:detail', ['id' => $page]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->category = $this->database->table('pages_categories');
        $template->setFile(__DIR__ . '/InsertFormControl.latte');
        $template->render();
    }
}