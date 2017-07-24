<?php
namespace Caloriscz\Events;

use Nette\Application\UI\Control;

class SignEventControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send request
     */
    function createComponentSignForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

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
            "page_id" => $this->presenter->getParameter("id"),
            "event" => $this->presenter->getParameter("event")
        ));

        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->onValidate[] = [$this, "signFormValidated"];
        $form->onSuccess[] = [$this, "signFormSucceeded"];
        return $form;
    }

    function signFormValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        $event = $this->database->table("events")->get($form->values->event);

        if (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage('Vyplňte jméno', "error");
            $this->presenter->redirect(this, array("id" => $event->pages_id, "event" => $event->id));
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->presenter->flashMessage('Vyplňte e-mail', "error");
            $this->presenter->redirect(this, array("id" => $event->pages_id, "event" => $event->id));
        }

        $eventSigned = $this->database->table("events_signed")->where(array(
            "email" => $form->values->email,
            "events_id" => $event->id,
        ));

        if ($eventSigned->count() > 0) {
            $this->presenter->flashMessage('Tento e-mail už byl použit při registraci', "error");
            $this->presenter->redirect(this, array("id" => $event->pages_id, "event" => $event->id));
        }

    }

    function signFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
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

        $this->presenter->redirect(":Admin:Events:signed", array("id" => $event->pages_id, "event" => $event->id));
    }

    protected function createComponentPageSlug()
    {
        $control = new \Caloriscz\Page\PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->events = $this->database->table("events")->where(array(
            "pages_id" => $this->presenter->getParameter("page_id"),
            "date_event > ?" => date('Y-m-d 12:00:00')
        ));

        $template->database = $this->database;

        $template->setFile(__DIR__ . '/SignEventControl.latte');

        $template->render();
    }

}