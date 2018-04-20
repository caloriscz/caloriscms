<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class EditEventControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit event
     */
    function createComponentEditEventForm()
    {
        $page = $this->database->table("pages")->get($this->presenter->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('event_id');
        $form->addText('date_event', 'dictionary.main.DateEventStarted')
            ->setAttribute("class", "form-control datepicker")
            ->setAttribute("style", "width: 110px; display: inline;")
            ->setHtmlId("event_start");
        $form->addSelect("hour_event")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 80px; display: inline;")
            ->setHtmlId("event_hour")
            ->setTranslator(null);
        $form->addSelect("minute_event")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 80px; display: inline;")
            ->setHtmlId("event_minute")
            ->setTranslator(null);
        $form->addText('date_event_end', 'dictionary.main.DateEventEnded')
            ->setAttribute("class", "form-control datepicker")
            ->setAttribute("style", "width: 110px; display: inline;")
            ->setHtmlId("event_end_date");
        $form->addSelect("hour_event_end")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 80px; display: inline;")
            ->setHtmlId("event_end_hour")
            ->setTranslator(null);
        $form->addSelect("minute_event_end")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 80px; display: inline;")
            ->setHtmlId("event_end_minute")
            ->setTranslator(null);
        $form->addText('price', 'dictionary.main.Price')
            ->setAttribute("class", "form-control");
        $form->addText('contact', 'dictionary.main.Place')
            ->setAttribute("class", "form-control");
        $form->addText('time_range', 'Rozsah')
            ->setAttribute("class", "form-control");
        $form->addCheckbox("allday", "dictionary.main.AllDayEvent");
        $form->addText('capacity', 'Kapacita')
            ->setAttribute("class", "form-control");
        $form->addText('capacity_start', 'Kapacita (přidat)')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success btn");

        $form->setDefaults(array(
            "id" => $page->id,
        ));

        $form->onSuccess[] = $this->editEventFormSucceeded;
        return $form;
    }

    function editEventFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT);

        $date_start = \DateTime::createFromFormat('j. n. Y', $form->values->date_event);
        $date1 = $date_start->format('Y-m-d');

        $date_start = \DateTime::createFromFormat('j. n. Y', $form->values->date_event_end);
        $date2 = $date_start->format('Y-m-d');

        if ($form->values->date_event == '') {
            $dateEvent = null;
        } else {
            if ($form->values->allday) {
                $dateEvent = $date1 . ' 00:00:00';
            } else {
                $dateEvent = $date1 . ' ' . $values["hour_event"] . ':' . $values["minute_event"];
            }
        }

        if ($form->values->date_event_end == '') {
            $dateEventEnd = null;
        } else {
            if ($form->values->allday) {
                $dateEventEnd = $date2 . ' 23:55:00';
            } else {
                $dateEventEnd = $date2 . ' ' . $values["hour_event_end"] . ':' . $values["minute_event_end"];
            }
        }

        $arr = array(
            "date_event" => $dateEvent,
            "date_event_end" => $dateEventEnd,
            "all_day" => $form->values->allday,
            "price" => $form->values->price,
            "capacity" => $form->values->capacity,
            "capacity_start" => $form->values->capacity_start,
            "time_range" => $form->values->time_range,
        );

        if ($form->values->contact) {
            $arr["contacts_id"] = $form->values->contact;
        }

        $this->database->table("events")->get($form->values->event_id)
            ->update($arr);

        $this->presenter->redirect(":Admin:Events:detail", array("id" => $form->values->id));
    }

    public function render($item)
    {
        $template = $this->template;
        $template->item = $item;
        $template->setFile(__DIR__ . '/EditEventControl.latte');

        $template->render();
    }

}
