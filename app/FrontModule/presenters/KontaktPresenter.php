<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Kontakt presenter.
 */
class KontaktPresenter extends BasePresenter
{

    /**
     * Send request
     */
    function createComponentSendForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal typePH";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("name")
                ->setAttribute("placeholder", "messages.helpdesk.name")
                ->setAttribute("class", "input-change");
        $form->addText("email")
                ->setAttribute("placeholder", "messages.helpdesk.email")
                ->setAttribute("class", "input-change")
                ->setOption("description", 1);
        $form->addText("phone")
                ->setAttribute("placeholder", "messages.helpdesk.phone")
                ->setAttribute("class", "input-change")
                ->setOption("description", 1);
        $form->addTextArea("message")
                ->setAttribute("placeholder", "messages.helpdesk.message")
                ->setAttribute("class", "input-change form-control");

        $form->setDefaults(array(
            "name" => $this->getParameter("name"),
            "email" => $this->getParameter("email"),
            "doctor" => $this->template->settings["contact_email"],
            "phone" => $this->getParameter("phone"),
            "message" => $this->getParameter("message"),
        ));
        $form->addSubmit("submitm", "messages.helpdesk.send")
                ->setAttribute("class", "btn btn-inverse btn-lg btn btn-default btn-eshop");

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
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

        $arrData = array(
            "subject" => $form->values->name,
            "email" => $form->values->email,
            "phone" => $form->values->phone,
            "doctor" => $form->values->doctor,
            "message" => $form->values->message,
            "ipaddress" => getenv('REMOTE_ADDR'),
            'date_created' => date("Y-m-d H:i"),
        );

        $this->database->table("helpdesk")
                ->insert($arrData);

        $latte = new \Latte\Engine;

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->template->settings["contact_email"]);
        $mail->addTo($this->template->settings["contact_email"]); // přehodit pozěji na maily doktorů
        $mail->setSubject("Zpráva: Dotaz");
        $mail->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . 'templates/Helpdesk/components/request-admin-email.latte', $arrData));

        $mailA = new \Nette\Mail\Message;
        $mailA->setFrom($this->template->settings["contact_email"]);
        $mailA->addTo($form->values->email);
        $mailA->setSubject("Zpráva: Dotaz");
        $mailA->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . 'templates/Helpdesk/components/request-customer-email.latte', $arrData));

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);
        $mailer->send($mailA);

        $this->flashMessage("Děkujeme za Vaši zprávu", "note");
        $this->redirect(":Front:Helpdesk:default");
    }

    function renderDefault()
    {
        $this->template->contacts = $this->database->table("contacts")
                        ->where(array("contacts_groups_id" => 8))->order("id");
    }

}
