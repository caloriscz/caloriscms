<?php

namespace App\Forms\Contact;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

class NewsletterFormControl extends Control
{

    /** @var Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Newsletter
     * @return \Nette\Forms\BootstrapUIForm
     */
    public function createComponentAddForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->addText('email');
        $form->addSubmit('submitm', 'messages.helpdesk.send');

        $form->onValidate[] = [$this, 'addFormValidated'];
        $form->onSuccess[] = [$this, 'addFormSucceeded'];
        return $form;
    }

    public function addFormValidated(BootstrapUIForm $form)
    {
        if (Validators::isEmail($form->getValues()->email) === false) {
            $this->onSave('Zadejte platnou e-mailovou adresu', 'error');
        }

        $contactNewsletter = $this->database->table('contacts')->where([
            'contacts_categories_id' => 3,
            'email' => $form->getValues()->email
        ]);

        if ($contactNewsletter->count() > 0) {
            $this->onSave('Váš e-mail byl již přidán', 'error');
        }
    }

    public function addFormSucceeded(BootstrapUIForm $form)
    {
        $arr = [
            'contacts_categories_id' => 3,
            'users_id' => null,
            'type' => 0,
            'email' => $form->values->email,
            'name' => $form->values->email,
        ];

        $this->database->table('contacts')->insert($arr);

        $this->onSave('Byli jste přihlášení k odběru newsletteru', 'success');
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/NewsletterFormControl.latte');
        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->render();
    }

}