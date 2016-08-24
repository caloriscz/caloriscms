<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Events presenter.
 */
class EventsPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();
    }

    /**
     * Edit event
     */
    function createComponentEditEventForm()
    {
        $page = $this->database->table("pages")->get($this->getParameter("id"));
        $event = $this->database->table("events")
            ->where(array("pages_id" => $page->id))->fetch();

        $form = $this->baseFormFactory->createUI();
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
        $form->addText('user_price', 'dictionary.main.Price')
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
            "contacts_id" => $form->values->contact,
            "time_range" => $form->values->time_range,
        );

        $this->database->table("events")->get($form->values->event_id)
            ->update($arr);

        $this->redirect(":Admin:Events:detail", array("id" => $form->values->id));
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

        $form = $this->baseFormFactory->createUI();
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
        $form->addText('user_price', 'dictionary.main.Price')
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
            "event_id" => $this->getParameter("id"),
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

        $this->redirect(":Admin:Events:detail", array("id" => $form->values->event_id));
    }

    function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText('title', 'dictionary.main.Title');

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new Model\Document($this->database);
        $doc->setType(3);
        $doc->setTitle($form->values->title);
        $doc->setSlug($form->values->title);
        $id = $doc->create($this->user->getId());

        Model\IO::directoryMake(APP_DIR . '/media/' . $id);

        $this->redirect(":Admin:Events:detail", array(
            "id" => $id,
        ));
    }

    /**
     * Delete page with all content
     */
    function handleDelete($id)
    {
        $doc = new Model\Document($this->database);
        $doc->delete($id);

        Model\IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->redirect(":Admin:Events:default", array("id" => null));
    }

    /**
     * Add the same event next week
     */
    function handleAddNextWeek($id)
    {
        $event = $this->database->table("events")->get($id);

        $this->database->table("events")
            ->insert(array(
                "date_event" => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s", strtotime($event->date_event)) . " +1 week")),
                "date_event_end" => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s", strtotime($event->date_event_end)) . " +1 week")),
                "all_day" => $event->all_day,
                "price" => $event->price,
                "contact" => $event->contact,
                "time_range" => $event->time_range,
                "pages_id" => $this->getParameter("page"),
            ));


        $this->redirect(":Admin:Events:detail", array("id" => $this->getParameter("page")));
    }

    /**
     * Add the same event next same day of the week
     */
    function handleAddNextSameDay($id)
    {
        $event = $this->database->table("events")->get($id);

        $this->database->table("events")
            ->insert(array(
                "date_event" => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . " +1 week")),
                "date_event_end" => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . " +1 week")),
                "all_day" => $event->all_day,
                "price" => $event->price,
                "contact" => $event->contact,
                "time_range" => $event->time_range,
                "pages_id" => $this->getParameter("page"),
            ));


        $this->redirect(":Admin:Events:detail", array("id" => $this->getParameter("page")));
    }

    /**
     * Image Upload
     */
    function createComponentImageEditForm()
    {
        $form = $this->baseFormFactory->createUI();
        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $image = $this->database->table("media")->get($this->getParameter("name"));

        $form->addHidden("id");
        $form->addHidden("name");
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "name" => $this->getParameter("name"),
            "description" => $image->description,
        ));

        $form->onSuccess[] = $this->imageEditFormSucceeded;
        return $form;
    }

    function imageEditFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("media")->get($form->values->name)
            ->update(array(
                'description' => $form->values->description,
            ));


        $this->redirect(":Admin:Events:imagesDetail", array(
            "id" => $form->values->id,
            "name" => $form->values->name,
        ));
    }

    /**
     * Delete one event
     */
    function handleDeleteDate($id)
    {
        $this->database->table("events")->get($id)->delete();

        $this->redirect(":Admin:Events:detail", array("id" => $this->getParameter("event")));
    }

    /**
     * Delete signed
     */
    function handleDeleteSigned($id)
    {
        $this->database->table('events_signed')->get($id)->delete();

        $this->redirect(":Admin:Events:signed", array("id" => $this->getParameter("page"), "event" => $id));
    }

    /**
     * Delete image
     */
    function handleDeleteImage($id)
    {
        $this->database->table("media")->get($id)->delete();

        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter("name"));
        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/tn/' . $this->getParameter("name"));

        $this->redirect(":Admin:Events:images", array("id" => $this->getParameter("name"),));
    }

    /**
     * Send request
     */
    function createComponentSignEventForm()
    {
        $form = $this->baseFormFactory->createPH();
        $form->getElementPrototype()->class = "contact-form";
        $form->addHidden("event");
        $form->addHidden("pages_id");
        $form->addRadioList("selection");
        $form->addText("name")
            ->setAttribute("placeholder", "messages.helpdesk.name");
        $form->addText("email")
            ->setAttribute("placeholder", "messages.helpdesk.email");
        $form->addText("phone")
            ->setAttribute("placeholder", "dictionary.main.Phone");
        $form->addTextArea("message")->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "page_id" => $this->getParameter("id"),
            "event" => $this->getParameter("event")
        ));

        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->onValidate[] = $this->signEventFormValidated;
        $form->onSuccess[] = $this->signEventFormSucceeded;
        return $form;
    }

    function signEventFormValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        $event = $this->database->table("events")->get($form->values->event);

        if (strlen($form->values->name) < 2) {
            $this->flashMessage('Vyplňte jméno', "error");
            $this->redirect(":Admin:Events:signed", array("id" => $event->pages_id, "event" => $event->id));
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->flashMessage('Vyplňte e-mail', "error");
            $this->redirect(":Admin:Events:signed", array("id" => $event->pages_id, "event" => $event->id));
        }

        $eventSigned = $this->database->table("events_signed")->where(array(
            "email" => $form->values->email,
            "events_id" => $event->id,
        ));

        if ($eventSigned->count() > 0) {
            $this->flashMessage('Tento e-mail už byl použit při registraci', "error");
            $this->redirect(":Admin:Events:signed", array("id" => $event->pages_id, "event" => $event->id));
        }

    }

    function signEventFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $event = $this->database->table("events")->get($form->values->event);

        $arrData = array(
            "name" => $form->values->name,
            "email" => $form->values->email,
            "phone" => $form->values->phone,
            "note" => $form->values->message,
            "ipaddress" => getenv('REMOTE_ADDR'),
            'date_created' => date("Y-m-d H:i:s"),
            'events_id' => $event->id,
        );

        $this->database->table("events_signed")->insert($arrData);

        $this->redirect(":Admin:Events:signed", array("id" => $event->pages_id, "event" => $event->id));
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        if ($this->getParameter('viewtype') == 'table') {
            $this->context->httpResponse->setCookie('viewtype', 'table', '180 days');
        } else {
            $this->context->httpResponse->setCookie('viewtype', 'calendar', '180 days');
        }


        $this->redirect(":Admin:Events:default", array("type" => $this->getParameter("type")));
    }

    public function renderDefault()
    {
        $type = 1;

        if ($type == 1) {
            $events = $this->database->table("pages")
                ->where("pages_types_id = 3");
        } else {
            $events = $this->database->table("pages")
                ->select(":events.id, pages.id, pages.title, pages.pages_types_id, :events.date_event, :events.date_event_end, 
            :events.all_day, :events.contact, public, DATEDIFF(NOW(), 
            :events.date_event) AS diffDate")
                ->where("pages_types_id = 3");
        }

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($events->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $type = 1;
        if ($type == 0) {
            $order = "diffDate DESC";
        } else {
            $order = "title";
        }

        $this->template->events = $events->order($order)->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();

        $this->template->viewtype = $this->context->httpRequest->getCookie('viewtype');
    }

    public function renderDetail()
    {
        $this->template->contacts = $this->database->table("contacts")->where("categories_id", 22);

        $this->template->hours = array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06",
            "07" => "07", "08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12",
            "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23");
        $this->template->minutes = array("00" => "00", "05" => "05", "10" => "10", "15" => "15", "20" => "20", "25" => "25", "30" => "30", "40" => "40", "45" => "45", "50" => "50", "55" => "55");

        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));

        $events = $this->database->table("events")->where("pages_id = ?", $this->getParameter("id"));

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($events->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));
        $this->template->database = $this->database;
        $this->template->events = $events->order("date_event")->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();
    }

    public function renderImages()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->images = $this->database->table("media")
            ->where(array("pages_id" => $this->getParameter("id"), "file_type" => 1));
    }

    public function renderImagesDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("name"));
        $this->template->image = $this->database->table("media")->get($this->getParameter("name"));
    }

    public function renderSigned()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $event = $this->database->table("events")->get($this->getParameter("event"));
        $this->template->event = $event;

        $this->template->signed = $this->database->table("events_signed")->where("events_id", $event->id);
    }

}