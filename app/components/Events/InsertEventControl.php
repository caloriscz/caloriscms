<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

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
     */
    function createComponentInsertEventForm()
    {
        $hours = array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06",
            "07" => "07", "08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12",
            "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23");
        $minutes = array("00" => "00", "05" => "05", "10" => "10", "15" => "15", "20" => "20", "25" => "25", "30" => "30", "40" => "40", "45" => "45", "50" => "50", "55" => "55");

        $contacts = $this->database->table("contacts")->where("categories_id", 22)->fetchPairs('id', 'company');

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('event_id');
        $form->addText('date_event', 'dictionary.main.DateEventStarted')
            ->setAttribute("class", "form-control datepicker")
            ->setHtmlId("event_start");
        $form->addSelect("hour_event", "", $hours)
            ->setAttribute("class", "form-control text-right")
            ->setHtmlId("event_hour")
            ->setTranslator(null);
        $form->addSelect("minute_event", "", $minutes)
            ->setAttribute("class", "form-control text-right")
            ->setHtmlId("event_minute")
            ->setTranslator(null);
        $form->addText('date_event_end', 'dictionary.main.DateEventEnded')
            ->setAttribute("class", "form-control datepicker")
            ->setHtmlId("event_end_date");
        $form->addSelect("hour_event_end", "", $hours)
            ->setAttribute("class", "form-control text-right")
            ->setHtmlId("event_end_hour")
            ->setTranslator(null);
        $form->addSelect("minute_event_end", "", $minutes)
            ->setAttribute("class", "form-control text-right")
            ->setHtmlId("event_end_minute")
            ->setTranslator(null);
        $form->addText('price', 'dictionary.main.Price')
            ->setAttribute("class", "form-control");
        $form->addSelect('contact', 'dictionary.main.Place', $contacts)
            ->setAttribute("class", "form-control");
        $form->addText('time_range', 'Rozsah')
            ->setAttribute("class", "form-control");
        $form->addText('capacity', 'Kapacita')
            ->setAttribute("class", "form-control");
        $form->addText('capacity_start', 'Kapacita (přidat)')
            ->setAttribute("class", "form-control");
        $form->addCheckbox("allday", "dictionary.main.AllDayEvent");

        $form->setDefaults(array(
            "event_id" => $this->presenter->getParameter("id"),
            "capacity_start" => 0,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success btn");

        $form->onSuccess[] = $this->insertEventFormSucceeded;
        return $form;
    }

    function insertEventFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $date_start = \DateTime::createFromFormat('j. n. Y', $form->values->date_event);
        $date1 = $date_start->format('Y-m-d');

        $date_end = \DateTime::createFromFormat('j. n. Y', $form->values->date_event_end);
        $date2 = $date_end->format('Y-m-d');

        if ($form->values->date_event == '') {
            $dateEvent = null;
        } else {
            if ($form->values->allday) {
                $dateEvent = $date1 . ' 00:00:00';
            } else {
                $dateEvent = $date1 . ' ' . $form->values->hour_event . ':' . $form->values->minute_event;
            }
        }

        if ($form->values->date_event_end == '') {
            $dateEventEnd = null;
        } else {
            if ($form->values->allday) {
                $dateEventEnd = $date2 . ' 23:55:00';
            } else {
                $dateEventEnd = $date2 . ' ' . $form->values->hour_event_end . ':' . $form->values->minute_event_end;
            }
        }

        $this->database->table("events")
            ->insert(array(
                "date_event" => $dateEvent,
                "date_event_end" => $dateEventEnd,
                "all_day" => $form->values->allday,
                "price" => $form->values->price,
                "contacts_id" => $form->values->contact,
                "time_range" => $form->values->time_range,
                "capacity" => $form->values->capacity,
                "capacity_start" => $form->values->capacity_start,
                "pages_id" => $form->values->event_id,
            ));

        $this->redirect(this, array("id" => $form->values->event_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertEventControl.latte');

        $template->render();
    }

}
