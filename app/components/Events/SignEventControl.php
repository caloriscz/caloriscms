<?php
namespace Caloriscz\Events;

use Caloriscz\Page\PageSlugControl;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

class SignEventControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send request
     */
    function createComponentSignForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->getElementPrototype()->class = 'contact-form';
        $form->addHidden('event');
        $form->addHidden('pages_id');
        $form->addRadioList('selection');
        $form->addText('name')
            ->setAttribute('placeholder', 'messages.helpdesk.name');
        $form->addText('email')
            ->setAttribute('placeholder', 'messages.helpdesk.email');
        $form->addText('phone')
            ->setAttribute('placeholder', 'dictionary.main.Phone');
        $form->addTextArea('message')->setAttribute('class', 'form-control');

        $form->setDefaults([
            'page_id' => $this->presenter->getParameter('id'),
            'event' => $this->presenter->getParameter('event')
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute('class', 'btn btn-success');

        $form->onValidate[] = [$this, 'signFormValidated'];
        $form->onSuccess[] = [$this, 'signFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    function signFormValidated(BootstrapUIForm $form)
    {
        $event = $this->database->table('events')->get($form->values->event);

        if (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage('Vyplňte jméno', 'error');
            $this->presenter->redirect('this', ['id' => $event->pages_id, 'event' => $event->id]);
        }

        if (false === Validators::isEmail($form->values->email)) {
            $this->presenter->flashMessage('Vyplňte e-mail', 'error');
            $this->presenter->redirect('this', ['id' => $event->pages_id, 'event' => $event->id]);
        }

        $eventSigned = $this->database->table('events_signed')->where([
            'email' => $form->values->email,
            'events_id' => $event->id,
        ]);

        if ($eventSigned->count() > 0) {
            $this->presenter->flashMessage('Tento e-mail už byl použit při registraci', 'error');
            $this->presenter->redirect('this', ['id' => $event->pages_id, 'event' => $event->id]);
        }

    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    function signFormSucceeded(BootstrapUIForm $form)
    {
        $event = $this->database->table('events')->get($form->values->event);

        $arrData = array(
            'name' => $form->values->name,
            'email' => $form->values->email,
            'phone' => $form->values->phone,
            'note' => $form->values->message,
            'ipaddress' => getenv('REMOTE_ADDR'),
            'date_created' => date('Y-m-d H:i:s'),
            'events_id' => $event->id,
        );

        $this->database->table('events_signed')->insert($arrData);

        $this->presenter->redirect(':Admin:Events:signed', array('id' => $event->pages_id, 'event' => $event->id));
    }

    protected function createComponentPageSlug()
    {
        $control = new PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->events = $this->database->table('events')->where(array(
            'pages_id' => $this->presenter->getParameter('page_id'),
            'date_event > ?' => date('Y-m-d 12:00:00')
        ));

        $template->database = $this->database;

        $template->setFile(__DIR__ . '/SignEventControl.latte');

        $template->render();
    }

}