<?php

namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertHourControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Insert hour
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('contact_id');
        $form->addSelect('day', 'Den v  týdnu', [
            1 => 'Pondělí', 2 => 'Úterý', 3 => 'Středa', 4 => 'Čtvrtek', 5 => 'Pátek', 6 => 'Sobota', 7 => 'Neděle'])
            ->setAttribute('class', 'form-control');
        $form->addText('hourstext', 'Hodiny (např. 14.00-20.00, jen objednaní)')
            ->setRequired('Vložte hodiny od-do nebo nějakou informaci');

        $contact = $this->database->table('contacts')->get($this->presenter->getParameter('id'));

        $form->setDefaults([
            'contact_id' => $contact->id,
        ]);

        $form->addSubmit('submitm', 'Vložit')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'validateFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateFormSucceeded(BootstrapUIForm $form): void
    {
        if (strlen($form->values->hourstext) < 1) {
            $this->getPresenter()->flashMessage('Vložte hodiny od-do nebo nějakou informaci', 'error');
            $this->getPresenter()->redirect('this', ["id" => $form->values->id]);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('contacts_openinghours')->insert([
            'day' => $form->values->day,
            'hourstext' => $form->values->hourstext,
            'contacts_id' => $form->values->contact_id,
        ]);

        $this->getPresenter()->redirect('this', ['id' => $form->values->contact_id]);
    }


    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertHourControl.latte');
        $this->template->render();
    }

}
