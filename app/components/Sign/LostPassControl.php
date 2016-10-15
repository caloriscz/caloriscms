<?php

namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class LostPassControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentSendForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("layer");
        $form->addText("email", "dictionary.main.Email");
        $form->addSubmit('submitm', 'dictionary.main.Send');

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $email = $form->getValues()->email;

        if ($form->values->layer == 'admin') {
            $lostPass = $this->database->table("helpdesk_emails")->where("template", "lostpass-admin")->fetch();
        } else {
            $lostPass = $this->database->table("helpdesk_emails")->where("template", "lostpass-member")->fetch();
        }

        if (!\Nette\Utils\Validators::isEmail($email)) {
            $this->presenter->flashMessage("Adresa je neplatná");
            $this->presenter->redirect(":Front:Sign:lostpass");
        }

        $passwordGenerate = \Nette\Utils\Strings::random(12, "987654321zyxwvutsrqponmlkjihgfedcba");

        if ($this->database->table('users')->where(array('email' => $email,))->count() == 0) {
            $this->flashMessage("E-mail nenalezen");
            $this->presenter->redirect(":Front:Sign:lostpass");
        }

        $member = new \App\Model\MemberModel($this->database);
        $member->setActivation($email, $passwordGenerate);

        $latte = new \Latte\Engine;
        $latte->setLoader(new \Latte\Loaders\StringLoader());

        $params = array(
            'code' => $passwordGenerate,
            'email' => $email,
            'settings' => $this->presenter->template->settings
        );

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->presenter->template->settings['contacts:email:hq'])
            ->addTo($email)
            ->setSubject("Informace o novém hesle")
            ->setHTMLBody($latte->renderToString($lostPass->body, $params));

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);

        $this->presenter->flashMessage('Informace o zapomenutém hesle odeslány', 'success');
        $this->presenter->redirect(this);
    }


    public function render($layer = 'front')
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LostPassControl.latte');
        $template->addon = $this->database->table("addons");
        $template->layer = $layer;
        $template->member = $this->presenter->template->member;

        $template->render();
    }

}
