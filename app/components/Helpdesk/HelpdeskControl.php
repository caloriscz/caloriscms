<?php
namespace Caloriscz\Helpdesk;

use Nette\Application\UI\Control;

class HelpdeskControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentSendForm()
    {
        $helpdesk = $this->database->table("helpdesk")->get(1);

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("name");
        $form->addText("email");

        if ($helpdesk->fill_phone > 0) {
            $form->addText("phone");
        }


        $form->addTextArea("message");

        $form->setDefaults(array(
            "name" => $this->getParameter("name"),
            "email" => $this->getParameter("email"),
            "phone" => $this->getParameter("phone"),
            "message" => $this->getParameter("message"),
        ));
        $form->addSubmit("submitm");

        $form->onValidate[] = [$this, "sendFormValidated"];
        $form->onSuccess[] = [$this, "sendFormSucceeded"];
        return $form;
    }

    function sendFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInName'), "error");
            $this->presenter->redirect(":Front:Contact:default");
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInEmail'), "error");
            $this->presenter->redirect(":Front:Contact:default");
        }

        if (strlen($form->values->message) < 2) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInMessage'), "error");
            $this->presenter->redirect(":Front:Contact:default");
        }
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->presenter->user->isLoggedIn()) {
            $arr["users_id"] = $this->presenter->template->member->id;
        }

        $params = array(
            'name' => $form->values->name,
            'phone' => $form->values->phone,
            'message' => $form->values->message,
        );

        $helpdesk = new \App\Model\Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(1);
        $helpdesk->setEmail($form->values->email);
        $helpdesk->setSettings($this->presenter->template->settings);
        $helpdesk->setParams($params);
        $helpdesk->send();

        $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.thanksForMessage'), "error");
        $this->presenter->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/HelpdeskControl.latte');


        $this->template->render();
    }

}
