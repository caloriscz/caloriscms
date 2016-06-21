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
     * Edit article
     */
    function createComponentEditEventForm()
    {
        $page = $this->database->table("pages")->get($this->getParameter("id"));
        $event = $this->database->table("events")
            ->where(array("pages_id" => $page->id))->fetch();

        $hours = array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06",
            "07" => "07", "08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12",
            "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23");
        $minutes = array("00" => "00", "05" => "05", "10" => "10", "15" => "15", "20" => "20", "25" => "25", "30" => "30", "40" => "40", "45" => "45", "50" => "50", "55" => "55");

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
        $form->addCheckbox("allday", "dictionary.main.AllDayEvent");
        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success btn-lg");

        $form->setDefaults(array(
            "id" => $page->id,
            "event_id" => $event->id,
            "date_event" => date("Y-m-d", strtotime($event->date_event)),
            "hour_event" => date("H", strtotime($event->date_event)),
            "minute_event" => date("i", strtotime($event->date_event)),
            "date_event_end" => date("Y-m-d", strtotime($event->date_event_end)),
            "hour_event_end" => date("H", strtotime($event->date_event_end)),
            "minute_event_end" => date("i", strtotime($event->date_event_end)),
            "allday" => $event->all_day,
        ));

        $form->onSuccess[] = $this->editEventFormSucceeded;
        return $form;
    }

    /**
     * Edit post
     */
    function editEventFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->date_event == '') {
            $dateEvent = null;
        } else {
            if ($form->values->allday) {
                $dateEvent = $form->values->date_event . ' 00:00:00';
            } else {
                $dateEvent = $form->values->date_event . ' ' . $form->values->hour_event . ':' . $form->values->minute_event;
            }
        }

        if ($form->values->date_event_end == '') {
            $dateEventEnd = null;
        } else {
            if ($form->values->allday) {
                $dateEventEnd = $form->values->date_event_end . ' 23:55:00';
            } else {
                $dateEventEnd = $form->values->date_event_end . ' ' . $form->values->hour_event_end . ':' . $form->values->minute_event_end;
            }
        }

        $this->database->table("events")->get($form->values->event_id)
            ->update(array(
                "date_event" => $dateEvent,
                "date_event_end" => $dateEventEnd,
                "all_day" => $form->values->allday,
            ));

        $this->redirect(":Admin:Events:detail", array("id" => $form->values->id));
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

        $this->database->table("events")
            ->insert(array(
                "pages_id" => $id,
            ));

        $this->redirect(":Admin:Events:detail", array(
            "id" => $id,
        ));
    }

    /**
     * Delete post
     */
    function handleDelete($id)
    {
        $doc = new Model\Document($this->database);
        $doc->delete($id);

        Model\IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->redirect(":Admin:Events:default", array("id" => null));
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

    public function renderDefault()
    {
        $events = $this->database->table("pages")
            ->select(":events.id, pages.id, pages.title, pages.pages_types_id, :events.date_event, :events.date_event_end, :events.all_day, public, DATEDIFF(NOW(), 
            :events.date_event) AS diffDate")
            ->where("pages_types_id = 3");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($events->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->events = $events->order("diffDate DESC")->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();
    }

    public function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderImages()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->images = $this->database->table("media")
            ->where(array("pages_id" => $this->getParameter("id"), "file_type" => 1));
    }

}