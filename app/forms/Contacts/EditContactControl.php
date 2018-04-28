<?php

namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

class EditContactControl extends Control
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
     * Edit contact
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $this->template->id = $this->getPresenter()->getParameter('id');

        $groups = $this->database->table('contacts_categories')->fetchPairs('id', 'title');
        $pages = $this->database->table('pages')->fetchPairs('id', 'title');

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->addHidden('contact_id');
        $form->addSelect('pages_id', '', $pages);
        $form->addText('name');
        $form->addText('company');
        $form->addText('post');
        $form->addRadioList('type', '', [0 => ' osoby', 1 => ' organizace']);
        $form->addText('tpost');
        $form->addText('email');
        $form->addText('phone');
        $form->addSelect('categories_id', '', $groups);
        $form->addText('street');
        $form->addText('zip');
        $form->addText('city');
        $form->addText('vatin');
        $form->addText('vatid');
        $form->addText('banking_account');
        $form->addText('dateofbirth');
        $form->addTextArea('notes');

        $contact = $this->database->table('contacts')->get($this->getPresenter()->getParameter('id'));

        $arr = [
            'contact_id' => $contact->id,
            'pages_id' => $contact->id,
            'name' => $contact->name,
            'company' => $contact->company,
            'post' => $contact->post,
            'type' => $contact->type,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'categories_id' => $contact->categories_id,
            'street' => $contact->street,
            'zip' => $contact->zip,
            'city' => $contact->city,
            'banking_account' => $contact->banking_account,
            'vatin' => $contact->vatin,
            'vatid' => $contact->vatid,
            'notes' => $contact->notes,
            'dateofbirth' => $contact->date_of_birth,
        ];

        $form->setDefaults($arr);

        $form->addSubmit('submitm');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'editFormValidated'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function editFormValidated(BootstrapUIForm $form): void
    {
        if (Validators::isEmail($form->values->email) === false && strlen($form->values->email) > 0) {
            $this->onSave($form->values->pages_id, $error = 1);
        }
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('contacts')
            ->where(['id' => $form->values->contact_id])
            ->update([
                'name' => $form->values->name,
                'pages_id' => $form->values->pages_id,
                'company' => $form->values->company,
                'post' => $form->values->post,
                'type' => $form->values->type,
                'email' => $form->values->email,
                'phone' => $form->values->phone,
                'contacts_categories_id' => $form->values->categories_id,
                'street' => $form->values->street,
                'zip' => $form->values->zip,
                'city' => $form->values->city,
                'vatin' => $form->values->vatin,
                'vatid' => $form->values->vatid,
                'banking_account' => $form->values->banking_account,
                'date_of_birth' => $form->values->dateofbirth,
                'notes' => $form->values->notes,
            ]);

        $this->onSave($form->values->contact_id);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditContactControl.latte');
        $this->template->render();
    }

}