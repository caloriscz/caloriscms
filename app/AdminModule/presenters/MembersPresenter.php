<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class MembersPresenter extends BasePresenter
{

    /**
     * Edit user data
     */
    function createComponentEditForm()
    {
        $form = $this->baseFormFactory->createUI();
        $user = $this->database->table("users")->get($this->getParameter("id"));

        $form->addHidden('id');
        $form->addRadioList("sex", "Pohlaví", array(1 => ' žena', 2 => ' muž'));
        $form->addRadioList("newsletter", "Odebírat newsletter", array(1 => ' ano', 2 => ' ne'));
        $form->addRadioList("state", "Stav účtu", array(1 => ' povolen', 2 => ' blokován'));

        $roles = $this->database->table("users_roles")->fetchPairs("id", "title");

        if ($this->template->member->username == 'admin') {
            $form->addSelect("role", "Role", $roles)
                    ->setAttribute("class", "form-control");
        }

        if ($this->template->settings['members:groups:enabled']) {
            $groups = $this->database->table("categories")->where(
                            "parent_id", $this->template->settings['members:group:categoryId']
                    )->fetchPairs("id", "title");

            $form->addSelect("group", "Skupina", $groups)
                    ->setAttribute("class", "form-control");
        }

        $arr = array(
            "id" => $this->getParameter("id"),
            "sex" => $user->sex,
            "newsletter" => $user->newsletter,
            "state" => $user->state,
            "role" => $user->role,
            "group" => $user->categories_id,
        );

        $form->setDefaults(array_filter($arr));

        $form->addSubmit('submitm', 'Uložit');
        $form->onSuccess[] = $this->editFormSucceeded;

        return $form;
    }

    function editFormSucceeded(Nette\Forms\BootstrapUIForm $form)
    {
        $arr = array(
            "sex" => $form->values->sex,
            "newsletter" => $form->values->newsletter,
            "state" => $form->values->state,
        );

        if ($this->template->member->username) {
            $arr["role"] = $form->values->role;
        }

        if ($this->template->settings['members:groups:enabled']) {
            $arr["categories_id"] = $form->values->group;
        }

        $this->database->table("users")->where(array(
            "id" => $form->values->id,
        ))->update($arr);

        $this->redirect(":Admin:Members:edit", array("" => $form->values->id));
    }

    /**
     * Insert new user
     */
    function createComponentInsertForm()
    {
        $roles = $this->database->table("users_roles")->fetchPairs("id", "title");
        $form = $this->baseFormFactory->createUI();
        $form->addText("username", "Uživatel")
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3);
        $form->addText("email", "E-mail");
        if ($this->template->member->username == 'admin') {
            $form->addSelect("role", "Role", $roles)
                    ->setAttribute("class", "form-control");
        }
        $form->addCheckbox("sendmail", "\xC2\xA0" . "Odeslat e-mail s přihlašovacími informacemi")
                ->setValue(1);

        $form->addSubmit('submitm', 'Vytvořit uživatele')->setAttribute("class", "btn btn-success");
        $form->onSuccess[] = $this->insertFormSucceeded;
        $form->onValidate[] = $this->insertFormValidated;

        return $form;
    }

    function insertFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        $member = new \App\Model\MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        if (\Nette\Utils\Validators::isEmail($form->values->email) == false) {
            $this->flashMessage('Napište správný formát e-mailu', 'error');
            $this->redirect(":Admin:Members:default", array("id" => null));
        } elseif ($emailExists > 0) {
            $this->flashMessage('Účet s tímto e-mailem již existuje', 'error');
            $this->redirect(":Admin:Members:default", array("id" => null));
        } elseif ($userExists > 0) {
            $this->flashMessage('Uživatel již existuje', 'error');
            $this->redirect(":Admin:Members:default", array("id" => null));
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $pwd = Nette\Utils\Random::generate(10);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);

        $userId = $this->database->table("users")
                ->insert(array(
            "email" => $form->values->email,
            "username" => $form->values->username,
            "password" => $pwdEncrypted,
            "date_created" => date("Y-m-d H:i:s"),
            "state" => 1,
        ));

        if ($form->values->sendmail) {
            $latte = new \Latte\Engine;
            $params = array(
                'username' => $form->values->username,
                'password' => $pwd,
                'settings' => $this->template->settings,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site:title"] . ' <' . $this->template->settings["contacts:email:hq"] . '>')
                    ->addTo($form->values->email)
                    ->setHTMLBody($latte->renderToString(substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Members/components/member-new-email.latte', $params));

            $this->mailer->send($mail);
        } else {
            $this->flashMessage("Heslo uživatele je $pwd", 'note');
        }

        $this->redirect(":Admin:Members:edit", array("id" => $userId));
    }

    /**
     * Send member login information
     */
    function createComponentSendLoginForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("user");
        $form->addCheckbox("sendmail", "\xC2\xA0" . "Odeslat e-mail s přihlašovacími informacemi")
                ->setValue(1);

        $form->setDefaults(array(
            "user" => $this->getParameter('id'),
        ));

        $form->addSubmit('submitm', 'Zaslat uživateli')->setAttribute("class", "btn btn-success");
        $form->onSuccess[] = $this->sendLoginFormSucceeded;

        return $form;
    }

    function sendLoginFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $pwd = Nette\Utils\Random::generate(10);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);

        $this->database->table("users")
                ->get($form->values->user)
                ->update(array(
                    "password" => $pwdEncrypted,
        ));

        $user = $this->database->table("users")->get($form->values->user);

        if ($form->values->sendmail) {
            $latte = new \Latte\Engine;
            $params = array(
                'username' => $user->username,
                'email' => $user->email,
                'password' => $pwd,
                'settings' => $this->template->settings,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site:title"] . ' <' . $this->template->settings["contacts:email:hq"] . '>')
                    ->addTo($user->email)
                    ->setHTMLBody($latte->renderToString(substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Members/components/member-resend-login.latte', $params));

            $this->mailer->send($mail);
        } else {
            $this->flashMessage("Heslo uživatele je $pwd", 'note');
        }

        $this->redirect(":Admin:Members:edit", array("id" => $form->values->user));
    }

    /**
     * User delete
     */
    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $member = $this->database->table("users")->get($id[$a]);

            if ($member->username == 'guest') {
                $this->flashMessage('Nemůžete smazat účet hosta', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            } elseif ($member->username == 'admin') {
                $this->flashMessage('Nemůžete smazat účet administratora', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            } elseif ($member->id == $this->user->getId()) {
                $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            }

            $this->database->table("users")->get($id[$a])->delete();
        }

        $this->redirect(":Admin:Members:default", array("id" => null));
    }

    /**
     * Contact for user delete
     */
    function handleDeleteContact($id)
    {
        try {
            $this->database->table("contacts")->get($id)->delete();
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 15) == 'SQLSTATE[23000]') {
                $message = ': Contact je potřebný v Objednávkách';
            }

            $this->flashMessage($this->translator->translate('messages.sign.CannotBeDeleted') . ': '. $message, "error");
        }

        $this->redirect(":Admin:Members:edit", array("id" => $this->getParameter("contact")));
    }

    /**
     * Insert contact
     */
    function createComponentInsertContactForm()
    {
        $form = $this->baseFormFactory->createPH();
        $form->addSubmit("submitm", "Vytvořit nový kontakt")
                ->setAttribute("class", "btn btn-success");
        $form->addHidden("user");
        $form->setDefaults(array("user" => $this->getParameter("id")));

        $form->onSuccess[] = $this->insertContactFormSucceeded;
        return $form;
    }

    function insertContactFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $contactId = $this->template->settings['categories:id:contact'];

        $id = $this->database->table("contacts")
                ->insert(array(
            "categories_id" => $contactId,
            "users_id" => $form->values->user,
        ));

        $this->redirect(":Admin:Contacts:detail", array("id" => $id));
    }

    protected function createComponentMembersGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->id == NULL) {
            $contacts = $this->database->table("users");
        } else {
            $contacts = $this->database->table("users")->where("categories_id", $this->id);
        }
        $grid->setTranslator($this->translator);
        $grid->setDataSource($contacts);
        $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'Název')
                ->setRenderer(function($item) {
                    $url = Nette\Utils\Html::el('a')->href($this->link('edit', array("id" => $item->id)))
                            ->setText($item->username);
                    return $url;
                })
                ->setSortable();
        $grid->addColumnText('email', $this->translator->translate('dictionary.main.Email'))
                ->setSortable();
        $grid->addColumnText('state', $this->translator->translate('dictionary.main.State'))->setRenderer(function($item) {
                    if ($item->date_created == 1) {
                        $text = 'povolen';
                    } else {
                        $text = 'blokován';
                    }
                    return $text;
                })
                ->setSortable();
        $grid->addColumnText('date_created', $this->translator->translate('dictionary.main.Date'))
                ->setRenderer(function($item) {
                    $date = date("j. n. Y", strtotime($item->date_created));

                    return $date;
                })
                ->setSortable();

        //$grid->setTranslator($this->translator);
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:members'];
    }

    public function renderEdit()
    {
        $this->template->members = $this->database->table("users")->get($this->getParameter("id"));
        $this->template->role = $this->user->getRoles();
    }

}
