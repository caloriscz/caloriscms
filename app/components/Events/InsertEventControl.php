<?php
namespace Caloriscz\Members;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Forms\BootstrapUIForm;

class InsertEventControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert event
     * @return BootstrapUIForm
     */
    function createComponentInsertEventForm()
    {
        $contacts = $this->database->table('contacts')->where('contacts_categories_id', 4);
        $contactsArr = [];

        foreach ($contacts as $contact) {
            $contactsArr[$contact->id] = $contact->name . ' ' . $contact->street . ' ' . $contact->zip . ' ' . $contact->city;
        }

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('event_id');
        $form->addText('date_event');
        $form->addText('date_event_end');
        $form->addText('price');
        $form->addSelect('contact', '', $contactsArr)
            ->setAttribute('class', 'form-control');
        $form->addText('time_range');
        $form->addText('capacity');
        $form->addText('capacity_start');
        $form->addCheckbox('allday');

        $form->setDefaults([
            'event_id' => $this->presenter->getParameter('id'),
            'capacity_start' => 0,
        ]);

        $form->addSubmit('submitm');

        $form->onSuccess[] = [$this, 'insertEventFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    function insertEventFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('events')->insert([
                'date_event' => $form->values->date_event,
                'date_event_end' => $form->values->date_event_end,
                'all_day' => $form->values->allday,
                'price' => $form->values->price,
                'contacts_id' => $form->values->contact,
                'time_range' => $form->values->time_range,
                'capacity' => $form->values->capacity,
                'capacity_start' => $form->values->capacity_start,
                'pages_id' => $form->values->event_id,
            ]);

        $this->redirect('this', ['id' => $form->values->event_id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/InsertEventControl.latte');

        $template->render();
    }

}
