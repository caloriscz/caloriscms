<?php

namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class InsertLanguageControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Insert new language
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('language', 'Jazyk');
        $form->addText('code', 'Kód');

        $form->addSubmit('send', 'Uložit')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function permissionValidated(): void
    {
        if ($this->presenter->template->member->users_roles->settings === 0) {
            $this->presenter->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $langExists = $this->database->table('languages')->where('title = ? OR code = ?',
            $form->values->language, $form->values->code);

        if ($langExists->count() > 0) {
            $this->presenter->flashMessage('Název jazyka nebo kód již existuje', 'error');
            $this->presenter->redirect('this');
        } else {
            $this->database->table('languages')->insert([
                'title' => $form->values->language,
                'code' => $form->values->code,
            ]);

            $this->presenter->redirect('this');
        }
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertLanguageControl.latte');

        $template->render();
    }

}