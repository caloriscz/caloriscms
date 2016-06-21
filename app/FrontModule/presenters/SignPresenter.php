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
        $form = $this->baseFormFactory->createUI();
        $form->addText('username', 'dictionary.main.User')
                ->setRequired('Vložte uživatelské jméno.');

        $form->addPassword('password', 'dictionary.main.Password')
                ->setRequired('Vložte heslo.');

        $form->addSubmit('send', 'dictionary.main.login')
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->signInFormSucceeded;
        return $form;
    }

    public function signInFormSucceeded($form, $values)
    {
        $oldid = session_id();
        $member = new \App\Model\MemberModel($this->database);
        $blocked = $member->getState($form->values->username);

        if ($blocked == FALSE) {
            $this->flashMessage("Musíte nejdříve ověřit váš účet", 'error');
            $this->redirect(':Front:Sign:in');
        }

        try {
            $this->getUser()->login($values->username, $values->password);
            $newid = session_id();

            if ($this->template->settings['store:enabled']) {
                $this->database->table("orders")->where(array("uid" => $oldid))->update(array("uid" => $newid));
            }


            $this->database->table("users")->get($this->user->getId())->update(array(
                "date_visited" => date("Y-m-d H:i:s"),
                "login_success" => new \Nette\Database\SqlLiteral("login_success + 1")
            ));

            $this->redirect(':Front:Homepage:default');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->database->table("users")->where(array("username" => $values->username))->update(array(
                "login_error" => new \Nette\Database\SqlLiteral("login_error + 1")
            ));

            $this->flashMessage("Nesprávné heslo", 'error');
            $this->redirect(':Front:Sign:in');
        }
    }

    public function createComponentSignUpForm()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addGroup("Uživatelské informace");
        $form->addText("email", "E-mail")
                ->addRule(\Nette\Forms\Form::EMAIL, 'Zadejte platný email.')
                ->setRequired('Vložte e-mail.');
        $form->addText("username", "Uživatelské jméno")
                ->setOption('description', (string) "Povoleny jsou pouze znaky a-z, 0-9 (pouze malá písmena)")
                ->setRequired('Zvolte si uživatelské jméno')
                ->addRule(\Nette\Forms\Form::PATTERN, 'Uživatelské jméno může obsahovat pouze znaky a-z, 0-9 (pouze malá písmena)', '[a-z0-9-]+')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte uživatelské jméno s alespoň %d znaky', 5)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte uživatelské jméno s nejvýše %d znaky', 40);

        if ($this->template->settings['members:groups:enabled']) {
            $groups = $this->database->table("categories")->where(
                            "parent_id", $this->template->settings['members:group:categoryId']
                    )->fetchPairs("id", "title");

            $form->addSelect("group", "Skupina", $groups)
                    ->setAttribute("class", "form-control");
        }

        $form->addPassword("pwd", "Heslo")
                ->setOption('description', (string) "6 - 40 znaků")
                ->setRequired('Zvolte si heslo')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte heslo s alespoň %d znaky', 6)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte heslo s nejvýše %d znaky', 40);

        $form->addPassword("pwd2", "Zopakovat heslo")
                ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
                ->addRule(\Nette\Forms\Form::EQUAL, 'Hesla se neshodují', $form['pwd']);

        if ($this->template->settings['members:signup:contactEnabled']) {
            $form->addGroup("Kontaktní údaje");
            $form->addText("name", "Jméno a příjmení")
                    ->setRequired('Zadejte jméno')
                    ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadejte jméno', 3)
                    ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zadejte jméno', 200);
            $form->addText("street", "dictionary.main.Street")
                    ->setRequired('Zadejte ulici')
                    ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadejte ulici', 3)
                    ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zadejte ulici', 200)
                    ->setAttribute("class", "smartform-street-and-number");
            $form->addText("zip", "PSČ")
                    ->setRequired('Zadejte PSČ')
                    ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadejte PSČ', 3)
                    ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zadejte PSČ', 20)
                    ->setAttribute("class", "smartform-city");
            $form->addText("city", "Město")
                    ->setRequired('Zadejte město')
                    ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadejte město', 1)
                    ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zadejte město', 80)
                    ->setAttribute("class", "smartform-zip");
        }

        if ($this->template->settings['members:signup:contactEnabled']) {
            $form->addGroup("Firemní informace");
            $form->addText("company", "dictionary.main.Company");
            $form->addText("vatin", "dictionary.main.VatIn");
            $form->addText("vatid", "dictionary.main.VatId");
        }


        $form->addCheckbox("newsletter", "\xC2\xA0" . "Chci odebírat zprávy?")
                ->setDefaultValue(TRUE);
        $form->addCheckbox("confirmation", "\xC2\xA0" . "Souhlasím s podmínkami")
                ->setRequired('Pro pokračování zaškrtněte Souhlasím s podmínkami');
        $form->setDefaults(array(
            "username" => $this->getParameter("user"),
            "email" => $this->getParameter("email"),
            "newsletter" => $this->getParameter("newsletter"),
            "name" => $this->getParameter("name"),
            "street" => $this->getParameter("street"),
            "city" => $this->getParameter("city"),
            "zip" => $this->getParameter("zip"),
        ));

        $form->addSubmit("submit", "Registrovat se")
                ->setAttribute("class", "btn-lg btn-cart-in");

        $form->setDefaults(array(
            "email" => $this->getParameter("email"),
            "username" => $this->getParameter("username"),
            "newsletter" => $this->getParameter("newsletter"),
        ));

        $form->onSuccess[] = $this->signUpFormSucceeded;
        $form->onValidate[] = $this->signUpFormValidated;

        return $form;
    }

    function signUpFormValidated(\Nette\Forms\BootstrapUIForm $form)
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
    }

    function signUpFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $activationCode = \Nette\Utils\Strings::random(12, "987654321zyxwvutsrqponmlkjihgfedcba");
        $password = \Nette\Security\Passwords::hash($form->values->pwd);

        $arr = array(
            "email" => $form->values->email,
            "username" => $form->values->username,
            "password" => $password,
            "activation" => $activationCode,
            "newsletter" => (bool) $form->values->newsletter,
            "state" => 0,
            "date_created" => date("Y-m-d H:i:s")
        );

        if ($this->template->settings['members:groups:enabled']) {
            $arr["categories_id"] = $form->values->group;
        }

        $userId = $this->database->table("users")
                ->insert($arr);

        $this->database->table("users")
                ->where(array("id" => $userId->id
                ))->update(array(
            "uid" => \Nette\Utils\Strings::padLeft($userId->id, 6, '0'),
        ));

        if ($this->template->settings['members:signup:contactEnabled']) {
            $arrContacts = array(
                "categories_id" => 44,
                "users_id" => $userId,
                "name" => $form->values->name,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "countries_id" => 1,
            );

            if ($this->template->settings['members:signup:companyEnabled']) {
                $arrContacts["company"] = $form->values->company;
                $arrContacts["vatin"] = $form->values->vatin;
                $arrContacts["vatid"] = $form->values->vatid;
            }

            $contactId = $this->database->table("contacts")->insert($arrContacts);
            $this->database->table("contacts")->get($contactId)->update(array("order" => $contactId));
        }

        if ($form->values->vatin) {
            $ares = new \h4kuna\Ares\Ares();
            $aresArr = $ares->loadData('04203992')->toArray();
        }

        $latte = new \Latte\Engine;
        $params = array(
            'username' => $form->values->username,
            'activationCode' => $activationCode,
            'settings' => $this->template->settings,
            'form' => $form,
            'aresArr' => $aresArr,
        );

        try {
            if ($this->template->settings['members:signup:confirmByAdmin']) {
                $mail = new \Nette\Mail\Message;
                $mail->setFrom($this->template->settings['contacts:email:hq'])
                        ->addTo($form->values->email)
                        ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Sign/components/signup-member-confirmbyadmin.latte', $params));

                $this->mailer->send($mail);

                $mailA = new \Nette\Mail\Message;
                $mailA->setFrom($this->template->settings['contacts:email:hq'])
                        ->addTo($this->template->settings['contacts:email:hq'])
                        ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Sign/components/signup-admin-confirm.latte', $params));

                $this->mailer->send($mailA);
                $this->flashMessage('Registrace byla dokončena. Po ověření Vám bude zaslán e-mail, po kterém se můžete přihlásit', 'note');
            } else {
                $mail = new \Nette\Mail\Message;
                $mail->setFrom($this->template->settings['contacts:email:hq'])
                        ->addTo($form->values->email)
                        ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Sign/components/signup-member.latte', $params));

                $this->mailer->send($mail);
                $this->flashMessage('Vaše registrace proběhla úspěšně. Po ověření se můžete přihlásit.', 'note');
            }

            $this->redirect(":Front:Sign:ed");
        } catch (Nette\Mail\SmtpException $e) {
            $this->flashMessage('E-mail nebyl odeslán' . $e->getMessage(), 'error');
            $this->redirect(":Front:Sign:up");
        }
    }

    /**
     * Form: Resets passwords in database, user fill in new password
     */
    public function createComponentResetForm()
    {
        $form = $this->baseFormFactory->createUI();
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
        $form = $this->baseFormFactory->createUI();
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
        $mail->setFrom($this->template->settings['site:title'] . ' ' . $this->template->settings['contacts:email:hq'])
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
        $form = $this->baseFormFactory->createUI();
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
        $oldid = session_id();

        $this->getUser()->logout();

        $newid = session_id();

        if ($this->template->settings['store:enabled']) {
            $this->database->table("orders")->where(array("uid" => $oldid))->update(array("uid" => $newid));
        }

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
