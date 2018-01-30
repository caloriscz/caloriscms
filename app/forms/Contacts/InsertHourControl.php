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
    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('contact_id');
        $form->addSelect('day', 'dictionary.main.DayOfTheWeek', array(
            1 => 'dictionary.days.Monday', 2 => 'dictionary.days.Tuesday', 3 => 'dictionary.days.Wednesday',
            4 => 'dictionary.days.Thursday', 5 => 'dictionary.days.Friday', 6 => 'dictionary.days.Saturday', 7 => 'dictionary.days.Sunday'))
            ->setAttribute('class', 'form-control');
        $form->addText('hourstext', 'Hodiny (např. 14.00-20.00, jen objednaní)')
            ->setRequired('Vložte hodiny od-do nebo nějakou informaci');

        $contact = $this->database->table('contacts')->get($this->presenter->getParameter('id'));

        $form->setDefaults(array(
            'contact_id' => $contact->id,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'validateFormSucceeded'];
        return $form;
    }

    public function validateFormSucceeded(BootstrapUIForm $form)
    {
        if (strlen($form->values->hourstext) < 1) {
            $this->getPresenter()->flashMessage('Vložte hodiny od-do nebo nějakou informaci', 'error');
            $this->getPresenter()->redirect(this, array("id" => $form->values->id));
        }
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('contacts_openinghours')->insert(array(
            'day' => $form->values->day,
            'hourstext' => $form->values->hourstext,
            'contacts_id' => $form->values->contact_id,
        ));

        $this->getPresenter()->redirect(this, ['id' => $form->values->contact_id]);
    }


    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertHourControl.latte');
        $this->template->render();
    }

}
