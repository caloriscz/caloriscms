<?php

namespace App\Forms\Profile;

use App\Model\Document;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertAddressControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('contacts_id');
        $form->addText('name', 'dictionary.main.Name');
        $form->addText('street', 'dictionary.main.Street');
        $form->addText('zip', 'dictionary.main.ZIP');
        $form->addText('city', 'dictionary.main.City');

        $form->setDefaults([
            'contacts_group_id' => 2,
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Save');
        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('contacts')->insert([
            'categories_id' => 5,
            'pages_id' => $page,
            'users_id' => $this->presenter->user->getId(),
            'name' => $form->values->name,
            'street' => $form->values->street,
            'zip' => $form->values->zip,
            'city' => $form->values->city,
        ]);

        $this->presenter->redirect(':Front:Profile:addresses');
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertAddressControl.latte');
        $this->template->render();
    }
}
