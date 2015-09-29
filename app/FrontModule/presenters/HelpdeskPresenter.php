<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HelpdeskPresenter extends BasePresenter
{

    /**
     * Send request
     */
    function createComponentSendForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("name", "messages.helpdesk.name")
                ->setAttribute("placeholder", "messages.helpdesk.name");
        $form->addText("email", "messages.helpdesk.email")
                ->setAttribute("placeholder", "messages.helpdesk.email")
                ->setOption("description", 1);
        $form->addText("phone", "messages.helpdesk.phone")
                ->setAttribute("placeholder", "messages.helpdesk.phone")
                ->setOption("description", 1);
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

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->name) < 2) {
            $this->flashMessage("Vyplňte Vaše skutečné jméno", "error");
            $this->redirect(":Front:Helpdesk:default");
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->flashMessage("Vyplňte pravý mail", "error");
            $this->redirect(":Front:Helpdesk:default");
        }

        if (strlen($form->values->message) < 2) {
            $this->flashMessage("Vyplňte zprávu", "error");
            $this->redirect(":Front:Helpdesk:default");
        }

        $this->database->table("helpdesk")
                ->insert(array(
                    "subject" => $form->values->name,
                    "email" => $form->values->email,
                    "phone" => $form->values->phone,
                    "message" => $form->values->message,
                    "ipaddress" => $this->context->httpRequest->getRemoteAddress(),
                    'date_created' => date("Y-m-d H:i"),
        ));

        $latte = new \Latte\Engine;
        $params = array(
            'name' => $form->values->name,
            'email' => $form->values->email,
            'phone' => $form->values->phone,
            'message' => $form->values->message,
            'ipaddress' => $this->context->httpRequest->getRemoteAddress(),
            'time' => date("Y-m-d H:i"),
        );

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->template->settings["contact_email"]);
        $mail->addTo($this->template->settings["contact_email"]);
        $mail->setSubject("Message: Request");
        $mail->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . 'templates/Helpdesk/components/request-admin-email.latte', $params));

        $mailA = new \Nette\Mail\Message;
        $mailA->setFrom($this->template->settings["contact_email"]);
        $mailA->addTo($form->values->email);
        $mailA->setSubject("Message: Request");
        $mailA->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . 'templates/Helpdesk/components/request-customer-email.latte', $params));

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);
        $mailer->send($mailA);

        $this->flashMessage("Děkujeme za Vaši zprávu", "note");
        $this->redirect(":Front:Helpdesk:default");
    }

}
