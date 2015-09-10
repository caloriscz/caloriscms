<?php

namespace App\FrontModule\Presenters;

use Nette,
    Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
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

    public function signInFormSucceeded($form, $values)
    {
        $member = new \App\Model\MemberModel($this->database);
        $blocked = $member->getState($form->values->username);

        if ($blocked == FALSE) {
            $this->flashMessage("Musíte nejdříve ověřit váš účet", 'error');
            $this->redirect(':Front:Sign:in');
        }

        try {
            $this->getUser()->login($values->username, $values->password);

            $this->redirect(':Front:Homepage:default');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage("Nesprávné heslo", 'error');
            $this->redirect(':Front:Sign:in');
        }
    }

    public function createComponentSignUpForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("name", "Jméno a příjmení")
                ->setRequired('Zadejte jméno')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadejte jméno', 3)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zadejte jméno', 200);
        $form->addText("email", "E-mail")
                ->addRule(\Nette\Forms\Form::EMAIL, 'Zadejte platný email.')
                ->setRequired('Vložte e-mail.');
        $form->addText("district", "Region (město)")
                ->setOption('description', (string) "Zadejte místo, které nemusí být místem vašeho bydliště, ale "
                        . "mohlo by sloužit jako cenná informace pro případné zájemce o osobní odběr (např. nejbližší"
                        . " velké město v okolí)")
                ->setRequired('Zadejte region nebo město.')
                ->addCondition(\Nette\Forms\Form::FILLED);

        $form->addText("username", "Uživatelské jméno")
                ->setOption('description', (string) "Povoleny jsou pouze znaky a-z, 0-9 (pouze malá písmena)")
                ->setRequired('Zvolte si uživatelské jméno')
                ->addRule(\Nette\Forms\Form::PATTERN, 'Uživatelské jméno může obsahovat pouze znaky a-z, 0-9 (pouze malá písmena)', '[a-z0-9-]+')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte uživatelské jméno s alespoň %d znaky', 5)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte uživatelské jméno s nejvýše %d znaky', 40);

        $form->addPassword("pwd", "Heslo")
                ->setOption('description', (string) "6 - 40 znaků")
                ->setRequired('Zvolte si heslo')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte heslo s alespoň %d znaky', 6)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte heslo s nejvýše %d znaky', 40);

        $form->addPassword("pwd2", "Zopakovat heslo")
                ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
                ->addRule(\Nette\Forms\Form::EQUAL, 'Hesla se neshodují', $form['pwd']);

        $form->addCheckbox("newsletter", "\xC2\xA0" . "Chci odebírat zprávy?")
                ->setDefaultValue(TRUE);
        $form->addCheckbox("confirmation", "\xC2\xA0" . "Souhlasím s podmínkami")
                ->setRequired('Pro pokračování zaškrtněte Souhlasím s podmínkami');
        $form->setDefaults(array(
            "name" => $this->getParameter("name"),
            "username" => $this->getParameter("user"),
            "email" => $this->getParameter("email"),
            "district" => $this->getParameter("district"),
            "newsletter" => $this->getParameter("newsletter"),
        ));

        $form->addSubmit("submit", "Registrovat se")
                ->setAttribute("class", "btn-large btn-info");

        $form->setDefaults(array(
            "email" => $this->getParameter("email"),
            "username" => $this->getParameter("username"),
            "district" => $this->getParameter("district"),
            "newsletter" => $this->getParameter("newsletter"),
        ));

        $form->onSuccess[] = $this->signUpFormSucceeded;

        return $form;
    }

    function signUpFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $userCorrects = preg_match("/^[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_]{4,40}$/", $form->getValues()->username, $userTest);

        $member = new \App\Model\MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        $formVal = $form->getValues(TRUE);

        if ($userExists > 0) {
            unset($formVal["username"]);
            $this->flashMessage('Uživatelské jméno již existuje', 'error');
        } elseif (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            unset($formVal["email"]);
            $this->flashMessage('Neplatná e-mailová adresa', 'error');
        } elseif ($emailExists > 0) {
            unset($formVal["email"]);
            $this->flashMessage('E-mail již existuje', 'error');
        } elseif ($userTest == 0) {
            unset($formVal["username"]);
            $this->flashMessage('Uživatelské jméno obsahuje nepovolené znaky', 'error');
        } elseif (strlen($form->values->name) < 2) {
            $this->flashMessage('Příliš krátké jméno', 'error');
        } else {
            $msg = 1;
        }

        if ($msg != 1) {
            unset($formVal["pwd"], $formVal["pwd2"], $formVal["confirmation"]);

            $this->redirect(":Front:Sign:up", $formVal);
        }

        $activationCode = \Nette\Utils\Strings::random(12, "987654321zyxwvutsrqponmlkjihgfedcba");
        $password = \Nette\Security\Passwords::hash($form->values->pwd);

        $userId = $this->database->table("users")
                ->insert(array(
            "email" => $form->values->email,
            "username" => $form->values->username,
            "name" => $form->values->name,
            "district" => $form->values->district,
            "password" => $password,
            "activation" => $activationCode,
            "newsletter" => (bool) $form->values->newsletter,
            "state" => 0,
            "date_created" => date("Y-m-d H:i:s")
        ));

        $this->database->table("users")
                ->where(array("id" => $userId->id
                ))->update(array(
            "uid" => 'MU' . \Nette\Utils\Strings::padLeft($userId->id, 6, '0'),
        ));

        $latte = new \Latte\Engine;
        $params = array(
            'username' => $form->values->username,
            'activationCode' => $activationCode,
        );

        try {
            $mail = new \Nette\Mail\Message;
            $mail->setFrom('Mimix <no-reply@mimix.cz>')
                    ->addTo($form->values->email)
                    ->setSubject("Informace o novém účtu")
                    ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Sign/components/email.latte', $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);

            $this->flashMessage('Vaše registrace proběhla úspěšně. Po ověření se můžete přihlásit.', 'note');
            $this->redirect(":Front:Sign:ed");
        } catch (Exception $e) {
            $this->flashMessage('E-mail nebyl odeslán' . $e->getMessage, 'error');
            $this->redirect(":Front:Sign:up");
        }
    }

    /**
     * Form: Resets passwords in database, user fill in new password
     */
    public function createComponentResetForm()
    {
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

    function resetFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
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
    function createComponentSendForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("email", "E-mail");
        $form->addSubmit('submit', 'Odeslat');

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
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
        $mail->setFrom('Mimix <no-reply@mimix.cz>')
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
    function createComponentVerifyForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->setMethod("GET");
        $form->addText("code", "Kód");
        $form->addText("email", "E-mail");
        $form->addSubmit('submitm', 'Ověřit');
        $form->onSuccess[] = $this->verifyFormSucceeded;

        return $form;
    }

    /**
     * Verify member's password
     */
    function verifyFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $userLoggedDb = $this->database->table('users')->where(array(
            'activation' => $this->getParameter("code"),
            'username' => $this->getParameter("user")
        ));

        if ($userLoggedDb->count() == 0) {
            $this->flashMessage('Aktivace není platná', 'error');
            $this->redirect(":Front:Sign:verify");
        } else {
            $this->database->table("users")->where(array(
                "activation" => $this->getParameter("code"),
                'username' => $this->getParameter("user")
            ))->update(array(
                "state" => 1
            ));

            $this->flashMessage('Úspěšně ověřeno. Nyní se můžete přihlásit.', 'note');
            $this->redirect(":Front:Sign:in");
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

    function renderIn()
    {
        if ($this->getParameter('msg') == 1) {
            $this->template->msg = TRUE;
        }
    }

    function renderResetpass()
    {
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
