<?php

namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class SignUpControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function createComponentSignUpForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);

        $form->addText("email")
            ->addRule(\Nette\Forms\Form::EMAIL, "messages.sign.enterValidEmail")
            ->setRequired('messages.sign.enterValidEmail');
        $form->addText("username")
            ->setOption('description', (string)"messages.sign.descriptionSymbolsEnabled")
            ->setRequired('messages.sign.enterValidUserName')
            ->addRule(\Nette\Forms\Form::PATTERN, "messages.sign.descriptionSymbolsEnabled", '[a-z0-9-]+')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.enterUserNameMinimum', 5)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.enterUserNameMaximum', 40);

        $groups = $this->database->table("users_categories")->fetchPairs("id", "title");

        $form->addSelect("group", "", $groups);

        $form->addPassword("pwd")
            ->setRequired('messages.sign.enterValidPassword')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.enterPasswordMinimum', 6)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.enterPasswordMaximum', 40);

        $form->addPassword("pwd2")
            ->setRequired('messages.sign.enterEmailForCheck')
            ->addRule(\Nette\Forms\Form::EQUAL, 'messages.sign.passwords-not-same', $form['pwd']);

        $form->addText("name")
            ->setRequired('messages.sign.enterValidName')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.EnterValidName', 3)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.EnterValidName', 200);
        $form->addText("street")
            ->setRequired('messages.sign.enterValidStreet')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.EnterValidStreet', 3)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.EnterValidStreet', 200);
        $form->addText("zip")
            ->setRequired('messages.sign.enterValidZip')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.EnterValidZip', 3)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.EnterValidZip', 20);
        $form->addText("city")
            ->setRequired('messages.sign.enterValidCity')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'messages.sign.EnterValidCity', 1)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'messages.sign.EnterValidCity', 80);

        $form->addText("company");
        $form->addText("vatin");
        $form->addText("vatid");
        $form->addText("message");


        $form->addCheckbox("newsletter", "\xC2\xA0" . "messages.sign.newsletterCheck")
            ->setDefaultValue(TRUE);
        $form->addCheckbox("confirmation", "\xC2\xA0" . "messages.sign.agreeWithConditions")
            ->setRequired('messages.sign.mustAgreeConditions');

        $form->setDefaults(array(
            "username" => $this->presenter->getParameter("user"),
            "email" => $this->presenter->getParameter("email"),
            "newsletter" => $this->presenter->getParameter("newsletter"),
            "name" => $this->presenter->getParameter("name"),
            "street" => $this->presenter->getParameter("street"),
            "city" => $this->presenter->getParameter("city"),
            "zip" => $this->presenter->getParameter("zip"),
        ));

        $form->addSubmit("submit", "dictionary.main.Signup")
            ->setAttribute("class", "btn-lg btn-cart-in");

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

        if ($userExists > 0 && $this->presenter->template->settings['members_username_as_email'] == 0) {
            unset($formVal["username"]);
            $this->presenter->flashMessage('messages.sign.userNameAlreadyExists', 'error');
        } elseif (\Nette\Utils\Validators::isEmail($form->values->email) == false) {
            unset($formVal["email"]);
            $this->presenter->flashMessage('messages.sign.enterValidEmail', 'error');
        } elseif ($emailExists > 0) {
            unset($formVal["email"]);
            $this->presenter->flashMessage('messages.sign.emailAlreadyExists', 'error');
        } elseif ($userTest == 0) {
            unset($formVal["username"]);
            $this->presenter->flashMessage('messages.sign.enterValidUserName', 'error');
        } elseif (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage('messages.sign.enterValidName', 'error');
        } else {
            $msg = 1;
        }

        if ($msg != 1) {
            unset($formVal["pwd"], $formVal["pwd2"], $formVal["confirmation"]);

            $this->presenter->redirect(this, $formVal);
        }
    }

    function signUpFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $activationCode = \Nette\Utils\Random::generate(12, "987654321zyxwvutsrqponmlkjihgfedcba");
        $password = \Nette\Security\Passwords::hash($form->values->pwd);

        if ($this->presenter->template->settings['members_username_as_email']) {
            $username = $form->values->email;
        } else {
            $username = $form->values->username;
        }

        $arr = array(
            "email" => $form->values->email,
            "username" => $username,
            "password" => $password,
            "activation" => $activationCode,
            "newsletter" => (bool)$form->values->newsletter,
            "state" => 0,
            "users_roles_id" => 4,
            "date_created" => date("Y-m-d H:i:s")
        );

        if ($this->presenter->template->settings['members:groups:enabled']) {
            $arr["users_categories_id"] = $form->values->group;
        }

        $userId = $this->database->table("users")->insert($arr);

        if ($this->presenter->template->settings['members:signup:contactEnabled']) {
            /* Associate page with contact */
            $doc = new \App\Model\Document($this->database);
            $doc->setType(5);
            $doc->setPublic(0);
            $doc->setTitle("contact-" . $form->values->title);
            $page = $doc->create($this->presenter->user->getId());
            \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

            $arrContacts = array(
                "contacts_categories_id" => 8,
                "pages_id" => $page,
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
            $aresArr = $ares->loadData($form->values->vatin)->toArray();
        }

        $params = array(
            "username" => $form->values->username,
            "activationCode" => $activationCode,
            "settings" => $this->presenter->template->settings,
            "form" => $form,
            "aresArr" => $aresArr,
        );

        try {
            $helpdesk = new \App\Model\Helpdesk($this->database, $this->presenter->mailer);

            if ($this->presenter->template->settings["members:signup:confirmByAdmin"]) {
                $helpdesk->setId(12);
                $message = "messages.sign.signupSuccessfulLoginWhenVerified";
            } else {
                $helpdesk->setId(3);
                $message = "messages.sign.SignupSuccessfulCanLogin";
            }

            $helpdesk->setEmail($form->values->email);
            $helpdesk->setSettings($this->presenter->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send(true);

            $this->onSave("/uspesna-registrace", $message, "note");

        } catch (\Nette\Mail\SmtpException $e) {
            $this->onSave("/registrace", "messages.sign.EmailNotSent" . $e->getMessage(), "error");
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . "/SignUpControl.latte");
        $template->settings = $this->presenter->template->settings;
        $template->addon = $this->database->table("addons");

        $template->render();
    }

}
