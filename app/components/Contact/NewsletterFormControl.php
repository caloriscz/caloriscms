<?php

use Nette\Application\UI\Control;

class NewsletterFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Newsletter
     * @return \Nette\Forms\BootstrapUIForm
     */
    public function createComponentAdd()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = '';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('email')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'messages.helpdesk.send')
            ->setAttribute('class', 'btn btn-yellow');

        $form->onValidate[] = [$this, 'addValidated'];
        $form->onSuccess[] = [$this, 'addSucceeded'];
        return $form;
    }

    public function addValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        $dbEmail = $this->database->table('contacts')->where(array(
            'categories_id' => 19,
            'email' => $form->values->email,
        ));

        if ($dbEmail->count() > 0) {
            $this->flashMessage('Váš e-mail byl již přidán');
            $this->presenter->redirect(this);
        }
    }

    public function addSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType(5);
        $doc->createSlug('contact-' . $form->values->email);
        $doc->setTitle($form->values->email);
        $page = $doc->create($this->presenter->user->getId());

        \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

        $arr = array(
            'users_id' => null,
            'pages_id' => $page,
            'type' => 0,
        );

        $arr['email'] = $form->values->email;
        $arr['name'] = $form->values->email;

        $this->database->table('contacts')
            ->insert($arr);

        $this->presenter->flashMessage('Byli jste přihlášení k odeběru newsletteru');
        $this->presenter->redirect(this);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/NewsletterFormControl.latte');
        $this->template->settings = $this->presenter->template->settings;
        $this->template->render();
    }

}