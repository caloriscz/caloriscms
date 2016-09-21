<?php
namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class SendLoginControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send member login information
     */
    function createComponentSendLoginForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("page_id");
        $form->addCheckbox("sendmail", "\xC2\xA0" . "Odeslat e-mail s přihlašovacími informacemi")
            ->setValue(0);

        $form->setDefaults(array(
            "page_id" => $this->presenter->getParameter('id'),
        ));

        $form->addSubmit('submitm', 'Zaslat uživateli')->setAttribute("class", "btn btn-success");
        $form->onSuccess[] = $this->sendLoginFormSucceeded;

        return $form;
    }

    function sendLoginFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $pwd = \Nette\Utils\Random::generate(10);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);

        $contacts = $this->database->table("contacts")->where("pages_id", $form->values->page_id);

        if ($contacts->count() == 0) {
            $this->presenter->flashMessage("Odpovídající kontakt nebyl nalezen", "error");
            $this->presenter->redirect(this, array("id" => $form->values->page_id));
        } else {
            $user = $contacts->fetch()->ref('users', 'users_id');
        }

        $this->database->table("users")->get($user->id)
            ->update(array(
                "password" => $pwdEncrypted,
            ));

        if ($form->values->sendmail) {
            $latte = new \Latte\Engine;
            $latte->setLoader(new \Latte\Loaders\StringLoader());
            $params = array(
                'username' => $user->username,
                'email' => $user->email,
                'password' => $pwd,
                'settings' => $this->presenter->template->settings,
            );

            $helpdesk = $this->database->table("helpdesk")->get(4);
            $helpdesk_resend_login = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(8);
            $helpdesk_resend = $latte->renderToString($helpdesk_resend_login->body, $params);

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->presenter->template->settings["site:title"] . ' <' . $this->presenter->template->settings["contacts:email:hq"] . '>')
                ->addTo($user->email)
                ->setHTMLBody($helpdesk_resend);

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
        } else {
            $this->presenter->flashMessage("pass", "success");
        }

        $this->presenter->redirect(this, array("id" => $form->values->page_id, "pdd" => $pwd));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SendLoginControl.latte');

        $template->render();
    }

}
