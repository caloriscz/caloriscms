<?php

class HelpdeskControl extends Nette\Application\UI\Control
{

    /** @var Nette\Database\Context */
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
        $form->addText("name", "messages.helpdesk.name")
            ->setAttribute("placeholder", "messages.helpdesk.name");
        $form->addText("email", "messages.helpdesk.email")
            ->setAttribute("placeholder", "messages.helpdesk.email")
            ->setOption("description", 1);

        if ($helpdesk->fill_phone > 0) {
            $form->addText("phone", "messages.helpdesk.phone")
                ->setAttribute("placeholder", "messages.helpdesk.phone")
                ->setOption("description", 1);
        }


        $form->addTextArea("message", "messages.helpdesk.message")
            ->setAttribute("placeholder", "messages.helpdesk.message")
            ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "name" => $this->getParameter("name"),
            "email" => $this->getParameter("email"),
            "phone" => $this->getParameter("phone"),
            "message" => $this->getParameter("message"),
        ));
        $form->addSubmit("submitm", "messages.helpdesk.send")
            ->setAttribute("class", "btn btn-primary");

        $form->onValidate[] = $this->sendFormValidated;
        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->name) < 2) {
            $this->flashMessage($this->translator->translate('messages.sign.fillInName'), "error");
            $this->redirect(":Front:Contact:default");
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->flashMessage($this->translator->translate('messages.sign.fillInEmail'), "error");
            $this->redirect(":Front:Contact:default");
        }

        if (strlen($form->values->message) < 2) {
            $this->flashMessage($this->translator->translate('messages.sign.fillInMessage'), "error");
            $this->redirect(":Front:Contact:default");
        }
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $helpdesk = $this->database->table("helpdesk")->get(1);
        $helpdesk_admin = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(1);
        $helpdesk_customer = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(2);

        $arr = array(
            "subject" => $form->values->name,
            "email" => $form->values->email,
            "message" => $form->values->message,
            "ipaddress" => getenv('REMOTE_ADDR'),
            "helpdesk_id" => 1,
            'date_created' => date("Y-m-d H:i"),
            'session_id' => session_id(),
        );

        if ($this->presenter->user->isLoggedIn()) {
            $arr["users_id"] = $this->presenter->template->member->id;
        }

        if ($helpdesk->fill_phone > 0) {
            $arr["phone"] = $form->values->phone;
        }

        $this->database->table("helpdesk_messages")
            ->insert($arr);

        $params = array(
            'name' => $form->values->name,
            'email' => $form->values->email,
            'phone' => $form->values->phone,
            'message' => $form->values->message,
            'ipaddress' => getenv('REMOTE_ADDR'),
            'time' => date("Y-m-d H:i"),
            'settings' => $this->presenter->template->settings,
        );

        $latte = new \Latte\Engine;
        $latte->setLoader(new Latte\Loaders\StringLoader());
        //$latte->setTempDirectory(__DIR__ . '/../temp');

        $email_admin = $latte->renderToString($helpdesk_admin->body, $params);
        $email_customer = (string)$latte->renderToString($helpdesk_customer->body, $params);

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->presenter->template->settings["contacts:email:hq"]);
        $mail->addTo($this->presenter->template->settings["contacts:email:hq"]);
        $mail->setHTMLBody($email_customer);

        $mailA = new \Nette\Mail\Message;
        $mailA->setFrom($this->presenter->template->settings["contacts:email:hq"]);
        $mailA->addTo($form->values->email);
        $mailA->setHTMLBody($email_admin);


        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);
        $mailer->send($mailA);


        $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.thanksForMessage'), "error");
        $this->presenter->redirect(":Front:Contact:default");
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/HelpdeskControl.latte');


        $this->template->render();
    }

}
