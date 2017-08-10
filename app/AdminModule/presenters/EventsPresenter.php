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


    protected function createComponentEditEventForm()
    {
        $control = new \Caloriscz\Members\EditEventControl($this->database);
        return $control;
    }

    protected function createComponentInsertEventForm()
    {
        $control = new \Caloriscz\Members\InsertEventControl($this->database);
        return $control;
    }
    
    protected function createComponentInsertEventPage()
    {
        $control = new \Caloriscz\Events\InsertEventPageControl($this->database);
        return $control;
    }

    protected function createComponentImageEditEvent()
    {
        $control = new \Caloriscz\Events\ImageEditControl($this->database);
        return $control;
    }
    
    protected function createComponentEventSign()
    {
        $control = new \Caloriscz\Events\SignEventControl($this->database);
        return $control;
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        if ($this->getParameter('viewtype') == 'table') {
            $this->response->setCookie('viewtype', 'table', '180 days');
        } else {
            $this->response->setCookie('viewtype', 'calendar', '180 days');
        }


        $this->redirect(":Admin:Events:default", array("type" => $this->getParameter("type")));
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

        $this->redirect(":Admin:Events:images", array("id" => $this->getParameter("page")));
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

        $this->template->viewtype = $this->request->getCookie('viewtype');
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
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
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