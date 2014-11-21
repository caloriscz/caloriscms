<?php

namespace App\FrontModule\Presenters;

use Nette,
    Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

    /** @persistent */
    public $backlink = '';

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Vložte uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Vložte heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = $this->signInFormSucceeded;
        return $form;
    }

    public function signInFormSucceeded($form, $values) {
        try {
            $this->getUser()->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->restoreRequest($this->backlink);
        $this->redirect('Homepage:');
    }

    public function createComponentSignUpForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("namefirst", "Jméno")
                ->setRequired('Příliš krátké jméno');
        $form->addText("name", "Příjmení")
                ->setRequired('Příliš krátké jméno');
        $form->addText("email", "E-mail")
                ->setType('email')->setRequired('E-mail');
        $form->addText("district", "Oblast (město)")
                ->setOption('description', (string) "Zadejte místo, které nemusí být místem vašeho bydliště, ale "
                        . "mohlo by sloužit jako cenná informace pro případné zájemce o osobní odběr (např. nejbližší"
                        . " velké město v okolí)");

        $form->addText("username", "Uživatelské jméno")
                ->setRequired('Uživatelské jméno');

        $form->addPassword("pwd", "Heslo")
                ->setRequired('Nesprávné heslo')
                ->setOption('description', (string) "6 - 40 znaků")
                ->addCondition(\Nette\Forms\Form::MIN_LENGTH, 6);

        $form->addPassword("pwd2", "Zopakovat heslo")
                ->setRequired('Nesprávné heslo')
                ->setOption('description', (string) "6 - 40 znaků");

        $form->addCheckbox("newsletter", "\xC2\xA0" . "Chcete odebírat zprávy?")
                ->setDefaultValue(TRUE);
        $form->addCheckbox("confirmation", "\xC2\xA0" . "Souhlasím s podmínkami");
        $form->setDefaults(array(
            "username" => $form->getValues()->user,
            "email" => $form->getValues()->email,
            "name" => $form->getValues()->name,
        ));

        $form->addSubmit("submit", "Registrovat se")
                ->setAttribute("class", "btn-large btn-info");
        $form->onSuccess[] = $this->signUpFormSucceeded;

        return $form;
    }

    function signUpFormSucceeded(\Nette\Forms\BootstrapUIForm $form) {
        if ($form->getValues()->confirmation !== "on") {
            $this->flashMessage("Musíte odsouhlasit podmínky!");
            $this->redirect(":Front:Sign:up");
        }

        $userCorrects = preg_match("/^[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_]{6,40}$/", $form->getValues()->username, $test);

        $member = new \App\Model\MemberModel($this->database);
        $userExists = $member->getUserName($form->getValues()->username);
        $emailExists = $member->getEmail($form->getValues()->email);

        if ($userExists > 0) {
            $this->flashMessage('Uživatelské jméno již existuje');
        } elseif (\Nette\Utils\Validators::isEmail($form->getValues()->email) == FALSE) {
            $this->flashMessage('Neplatná e-mailová adresa');
        } elseif ($emailExists > 0) {
            $this->flashMessage('E-mail již existuje');
        } elseif ($userCorrects == 0) {
            $this->flashMessage('Uživatelské jméno obsahuje nepovolené znaky');
        } elseif (strcmp($form->getValues()->pwd, $form->getValues()->pwd2) <> 0) {
            $this->flashMessage('Hesla se neshodují');
        } elseif (strlen($form->getValues()->name) < 2) {
            $this->flashMessage('Příliš krátké jméno');
        } elseif (strlen($form->getValues()->namefirst) < 2) {
            $this->flashMessage('Příliš krátké jméno');
        } else {
            $msg = 1;
        }

        if ($msg == '') {
            $this->redirect(":Front:Sign:up");
        }

        $activationCode = \Nette\Utils\Strings::random(12, "987654321zyxwvutsrqponmlkjihgfedcba");
        $password = \Nette\Security\Passwords::hash($form->getValues()->pwd);

        $this->database->table("users")
                ->insert(array(
                    "email" => $form->getValues()->email,
                    "uid" => 'MU' . str_pad(abs(substr($this->database->table("users")->max("uid"), 2)) + 1, 6, "0", STR_PAD_LEFT),
                    "username" => $form->getValues()->username,
                    "name" => $form->getValues()->name,
                    "namefirst" => $form->getValues()->namefirst,
                    "district" => $form->getValues()->district,
                    "password" => $password,
                    "activation" => $activationCode,
                    "newsletter" => (bool) $form->getValues()->newsletter,
                    "state" => 0,
                    "date_created" => date("Y-m-d H:i:s")
        ));

        $latte = new \Latte\Engine;
        $params = array(
            'activationCode' => $activationCode,
        );

        try {
            $mail = new \Nette\Mail\Message;
            $mail->setFrom('Mimix <info@mimix.cz>')
                    ->addTo($form->getValues()->email)
                    ->setSubject("Informace o novém účtu")
                    ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Sign/components/email.latte', $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);

            $msg = 'Registrace byla úspěšná';
        } catch (Exception $e) {
            $msg = 'Neznámá chyba. E-mail nebyl odeslán' . $e->getMessage;
        }

        $this->flashMessage($msg);
        $this->redirect(":Front:Sign:up");
    }

    /**
     * Form: Resets passwords in database, user fill in new password
     */
    public function createComponentResetForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("email");
        $form->addHidden("code");
        $form->addPassword("password", "Nové heslo");
        $form->addPassword("password2", "Zopakujte nové heslo");
        $form->addSubmit('name', 'Změnit');
        $form->setDefaults(array(
            "email" => $this->getParameter("email"),
            "code" => $this->getParameter("code"),
        ));

        $form->onSuccess[] = $this->resetFormSucceeded;
        return $form;
    }

    function resetFormSucceeded(\Nette\Forms\BootstrapUIForm $form) {
        $email = $form->getValues()->email;
        $emailExists = $this->database->table('users')->where(array(
                    'email' => $email,
                    'activation' => $form->getValues()->code,
                ))->fetch();

        if (count($emailExists) == 0) {
            $msg = 'E-mail nenalezen';
        } elseif ($emailExists["activation"] == '') {
            $msg = 'Aktivace není platná';
        } elseif (strcmp($form->getValues()->password, $form->getValues()->password2) <> 0) {
            $msg = 'Hesla se neshodují';
        } else {
            $msg = 'Vytvořili jste nové heslo. Můžete se přihlásit.';
            $this->database->table("users")->where(array(
                "email" => $email,
            ))->update(array(
                "activation" => NULL,
                "password" => \Nette\Security\Passwords::hash($form->getValues()->password),
            ));
        }

        $this->flashMessage($msg, 'success');
        $this->redirect(":Front:Sign:in");
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentSendForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("email", "E-mail");
        $form->addSubmit('submit', 'Odeslat');

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form) {
        $email = $form->getValues()->email;

        if (!\Nette\Utils\Validators::isEmail($email)) {
            $this->flashMessage("Adresa je neplatná");
            $this->redirect(":Front:Sign:lostpass");
        }

        $passwordGenerate = \Nette\Utils\Strings::random(12, "987654321zyxwvutsrqponmlkjihgfedcba");

        if ($this->database->table('users')->where(array('email' => $email,))->count() == 0) {
            $this->flashMessage("E-mail nenalezen");
            $this->redirect(":Front:Sign:lostpass");
        }

        $member = new \App\Model\MemberModel($this->database);
        $member->setActivation($email, $passwordGenerate);

        $latte = new \Latte\Engine;
        $params = array(
            'code' => $passwordGenerate,
            'email' => $email,
        );

        $mail = new \Nette\Mail\Message;
        $mail->setFrom('Mimix <info@mimix.cz>')
                ->addTo($email)
                ->setSubject("Mimix: Informace o novém hesle")
                ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) .
                                '/templates/Sign/components/message-lostpass.latte', $params));

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);

        $this->flashMessage('Informace o zapomenutém hesle odeslány', 'success');
        $this->redirect(":Front:Sign:lostpass");
    }

    /**
     * Verification form for invitations
     */
    function createComponentVerifyForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("code", "[cal:www.t(InvitationCode;members) /]");
        $form->addText("email", "[cal:www.t(E-mail) /]");
        $form->addSubmit('submit', '[cal:www.t(Verify;members) /]');

        return $form;
    }

    /**
     * Verify member's password
     */
    function verifySucceeded(\Nette\Forms\BootstrapUIForm $form) {
        $activation = $form->getValues()->activation;

        $userLogged = $this->database->table('users')->where(array(
                    'activation' => $activation
                ))->fetch();

        if ($userLogged->count() == 0) {
            $message = 'Aktivace není platná';
        } else {
            $this->database->table("users")->where(array(
                "activation" => $activation,
            ))->update(array(
                "state" => 1
            ));

            $message = 'Úspěšně ověřeno. Nyní se můžete přihlásit.';
        }

        $this->flashMessage($message);
        $this->redirect(":Front:Sign:verify");
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

    function renderResetpass() {
        $activation = $this->database->table("users")->where(array(
            "email" => $this->getParameter("email"),
            "activation" => $this->getParameter("code"),
        ));

        if ($activation->count() > 0) {
            $this->template->activationValid = TRUE;
        } else {
            $this->template->activationValid = FALSE;
        }
    }

}
