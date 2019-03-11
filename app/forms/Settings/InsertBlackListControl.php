<?php

namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertBlackListControl extends Control
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
        $form->setTranslator($this->presenter->translator);

        if ($this->presenter->getParameter('type') === '') {
            $pageType = 9;
        } else {
            $pageType = $this->presenter->getParameter('type');
        }

        $form->addHidden('id');
        $form->addText('title');

        $form->setDefaults([
            'section' => $pageType,
        ]);

        $form->addSubmit('submit', 'dictionary.main.Create');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('blacklist')->insert(['title' => $form->values->title]);

        $this->presenter->redirect('this');
    }

    /**
     *
     * @param type $id
     * @param type $type Insert blaclist word or sentence
     */
    public function render($id = null, $type = null)
    {
        $this->template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $this->template->args = $getParams;

        $this->template->setFile(__DIR__ . '/InsertBlackListControl.latte');

        $this->template->render();
    }

}