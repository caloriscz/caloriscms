<?php

namespace Caloriscz\Members;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditEventControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit event
     */
    function createComponentEditEventForm()
    {
        $event = $this->database->table('events')->get($this->getPresenter()->getParameter('id'));


        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('event_id');
        $form->addText('date_event', 'dictionary.main.DateEventStarted')
            ->setAttribute('class', 'form-control datepicker')
            ->setAttribute('style', 'width: 110px; display: inline;')
            ->setHtmlId('event_start');
        $form->addText('date_event_end', 'dictionary.main.DateEventEnded')
            ->setAttribute('class', 'form-control datepicker')
            ->setAttribute('style', 'width: 110px; display: inline;')
            ->setHtmlId('event_end_date');
        $form->addText('price', 'dictionary.main.Price')
            ->setAttribute('class', 'form-control');
        $form->addText('contact', 'dictionary.main.Place')
            ->setAttribute('class', 'form-control');
        $form->addText('time_range', 'Rozsah')
            ->setAttribute('class', 'form-control');
        $form->addCheckbox('allday', 'dictionary.main.AllDayEvent');
        $form->addText('capacity', 'Kapacita')
            ->setAttribute('class', 'form-control');
        $form->addText('capacity_start', 'Kapacita (přidat)')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-success btn');

        $form->setDefaults([
            'id' => $event->pages_id,
            'event_id' => $event->id,
        ]);

        $form->onSuccess[] = [$this, 'editEventFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    function editEventFormSucceeded(BootstrapUIForm $form)
    {
        $arr = [
            'date_event' => $form->values->date_event,
            'date_event_end' => $form->values->date_event_end,
            'all_day' => $form->values->allday,
            'price' => $form->values->price,
            'capacity' => $form->values->capacity,
            'capacity_start' => $form->values->capacity_start,
            'time_range' => $form->values->time_range,
        ];

        if ($form->values->contact) {
            $arr['contacts_id'] = $form->values->contact;
        }

        $this->database->table('events')->get($form->values->event_id)
            ->update($arr);

        $this->presenter->redirect(':Admin:Events:detail', ['id' => $form->values->id]);
    }

    public function render($item)
    {
        $template = $this->getTemplate();
        $template->event = $this->database->table('events')->get($this->getPresenter()->getParameter('id'));
        $template->setFile(__DIR__ . '/EditEventControl.latte');

        $template->render();
    }

}
