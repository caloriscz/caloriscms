<?php

namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class SignUpControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function createComponentSignUpForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addGroup("Uživatelské informace");
        $form->addText("email", "E-mail")
            ->addRule(\Nette\Forms\Form::EMAIL, 'Zadejte platný email.')
            ->setRequired('Vložte e-mail.');
        $form->addText("username", "Uživatelské jméno")
            ->setOption('description', (string)"Povoleny jsou pouze znaky a-z, 0-9 (pouze malá písmena)")
            ->setRequired('Zvolte si uživatelské jméno')
            ->addRule(\Nette\Forms\Form::PATTERN, 'Uživatelské jméno může obsahovat pouze znaky a-z, 0-9 (pouze malá písmena)', '[a-z0-9-]+')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte uživatelské jméno s alespoň %d znaky', 5)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte uživatelské jméno s nejvýše %d znaky', 40);

        if ($this->presenter->template->settings['members:groups:enabled']) {
            $groups = $this->database->table("categories")->where(
                "parent_id", $this->presenter->template->settings['members:group:categoryId']
            )->fetchPairs("id", "title");

            $form->addSelect("group", "Skupina", $groups)
                ->setAttribute("class", "form-control");
        }

        $form->addPassword("pwd", "dictionary.main.Password")
            ->setOption('description', (string)"6 - 40 znaků")
            ->setRequired('Zvolte si heslo')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte heslo s alespoň %d znaky', 6)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte heslo s nejvýše %d znaky', 40);

        $form->addPassword("pwd2", "Zopakovat heslo")
            ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
            ->addRule(\Nette\Forms\Form::EQUAL, 'Hesla se neshodují', $form['pwd']);

        if ($this->presenter->template->settings['members:signup:contactEnabled']) {
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

        if ($this->presenter->template->settings['members:signup:contactEnabled']) {
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
            "username" => $this->presenter->getParameter("user"),
            "email" => $this->presenter->getParameter("email"),
            "newsletter" => $this->presenter->getParameter("newsletter"),
            "name" => $this->presenter->getParameter("name"),
            "street" => $this->presenter->getParameter("street"),
            "city" => $this->presenter->getParameter("city"),
            "zip" => $this->presenter->getParameter("zip"),
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
            $this->presenter->flashMessage('Uživatelské jméno již existuje', 'error');
        } elseif (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            unset($formVal["email"]);
            $this->presenter->flashMessage('Neplatná e-mailová adresa', 'error');
        } elseif ($emailExists > 0) {
            unset($formVal["email"]);
            $this->presenter->flashMessage('E-mail již existuje', 'error');
        } elseif ($userTest == 0) {
            unset($formVal["username"]);
            $this->presenter->flashMessage('Uživatelské jméno obsahuje nepovolené znaky', 'error');
        } elseif (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage('Příliš krátké jméno', 'error');
        } else {
            $msg = 1;
        }

        if ($msg != 1) {
            unset($formVal["pwd"], $formVal["pwd2"], $formVal["confirmation"]);

            $this->presenter->redirect(":Front:Sign:up", $formVal);
        }
    }

    function signUpFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $activationCode = \Nette\Utils\Random::generate(12, "987654321zyxwvutsrqponmlkjihgfedcba");
        $password = \Nette\Security\Passwords::hash($form->values->pwd);

        $arr = array(
            "email" => $form->values->email,
            "username" => $form->values->username,
            "password" => $password,
            "activation" => $activationCode,
            "newsletter" => (bool)$form->values->newsletter,
            "state" => 0,
            "users_roles_id" => 4,
            "date_created" => date("Y-m-d H:i:s")
        );

        if ($this->presenter->template->settings['members:groups:enabled']) {
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

            if ($this->presenter->template->settings['members:signup:companyEnabled']) {
                $arrContacts["company"] = $form->values->company;
                $arrContacts["vatin"] = $form->values->vatin;
                $arrContacts["vatid"] = $form->values->vatid;
            }

            $contactId = $this->database->table("contacts")->insert($arrContacts);
            $this->database->table("contacts")->get($contactId)->update(array("order" => $contactId));
        }

        if ($form->values->vatin) {
            $ares = new \h4kuna\Ares\Ares();
            $aresArr = $ares->loadData('')->toArray();
        }

        $latte = new \Latte\Engine;
        $latte->setLoader(new \Latte\Loaders\StringLoader());
        $params = array(
            'username' => $form->values->username,
            'activationCode' => $activationCode,
            'settings' => $this->presenter->template->settings,
            'form' => $form,
            'aresArr' => $aresArr,
        );

        $helpdesk = $this->database->table("helpdesk")->get(3);
        $helpdesk_signup_member = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(5);
        $helpdesk_signup_confirmbyadmin = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(6);
        $helpdesk_signup_adminconfirm = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(7);

        try {
            if ($this->presenter->template->settings['members:signup:confirmByAdmin']) {
                $email_signup_confirmbyamin = $latte->renderToString($helpdesk_signup_confirmbyadmin->body, $params);
                $email_signup_adminconfirm = $latte->renderToString($helpdesk_signup_adminconfirm->body, $params);

                $mail = new \Nette\Mail\Message;
                $mail->setFrom($this->presenter->template->settings['contacts:email:hq'])
                    ->addTo($form->values->email)
                    ->setHTMLBody($email_signup_confirmbyamin);

                $this->presenter->mailer->send($mail);

                $mailA = new \Nette\Mail\Message;
                $mailA->setFrom($this->presenter->template->settings['contacts:email:hq'])
                    ->addTo($this->presenter->template->settings['contacts:email:hq'])
                    ->setHTMLBody($email_signup_adminconfirm);

                $this->presenter->mailer->send($mailA);
                $this->flashMessage('Registrace byla dokončena. Po ověření Vám bude zaslán e-mail, po kterém se můžete přihlásit', 'note');
            } else {
                $email_signup_member = $latte->renderToString($helpdesk_signup_member->body, $params);

                $mail = new \Nette\Mail\Message;
                $mail->setFrom($this->presenter->template->settings['contacts:email:hq'])
                    ->addTo($form->values->email)
                    ->setHTMLBody($email_signup_member);

                $this->presenter->mailer->send($mail);
                $this->presenter->flashMessage('Vaše registrace proběhla úspěšně. Po ověření se můžete přihlásit.', 'note');
            }

            $this->presenter->redirect(":Front:Sign:ed");
        } catch (\Nette\Mail\SmtpException $e) {
            $this->presenter->flashMessage('E-mail nebyl odeslán' . $e->getMessage(), 'error');
            $this->presenter->redirect(":Front:Sign:up");
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SignUpControl.latte');
        $template->addon = $this->database->table("addons");

        $template->render();
    }

}
