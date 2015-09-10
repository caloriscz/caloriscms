<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class MembersPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Edit user data
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $name = $this->database->table("users")->get($this->getParameter("id"))->name;

        $form->addHidden('id');
        $form->addText("name", "Jméno");
        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "name" => $name,
        ));

        $form->addSubmit('submitm', 'Uložit');
        $form->onSuccess[] = $this->editFormSucceeded;

        return $form;
    }

    function editFormSucceeded(Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->where(array(
            "id" => $form->values->id,
        ))->update(array(
            "name" => $form->values->name,
        ));

        $this->redirect(":Admin:Members:edit", array("" => $form->values->id));
    }

    /**
     * Insert new user
     */
    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("username", "Uživatel")
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3)
                ->setAttribute("style", "width: 250px;");
        $form->addText("email", "E-mail")
                ->setAttribute("style", "width: 250px;");
        $form->addCheckbox("sendmail", "\xC2\xA0" . "Odeslat e-mail s přihlašovacími informacemi")
                ->setValue(1);

        $form->addSubmit('submitm', 'Vytvořit uživatele');
        $form->onSuccess[] = $this->insertFormSucceeded;

        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $member = new \App\Model\MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        if (\Nette\Utils\Validators::isEmail($form->values->email) == false) {
            $this->flashMessage('Napište správný formát e-mailu', 'error');
            $this->redirect(":Admin:Members:default");
        } elseif ($emailExists > 0) {
            $this->flashMessage('Účet s tímto e-mailem již exituje', 'error');
            $this->redirect(":Admin:Members:default");
        } elseif ($userExists > 0) {
            $this->flashMessage('Uživatel již exituje', 'error');
            $this->redirect(":Admin:Members:default");
        }

        $pwd = Nette\Utils\Random::generate(8);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);

        $userId = $this->database->table("users")
                ->insert(array(
            "email" => $form->values->email,
            "username" => $form->values->username,
            "password" => $pwdEncrypted,
            "date_created" => date("Y-m-d H:i:s")
        ));

        if ($form->values->sendmail) {
            $latte = new \Latte\Engine;
            $params = array(
                'username' => $form->values->username,
                'password' => $pwd,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site_title"] . ' <' . $this->template->settings["contact_email"] . '>')
                    ->addTo($form->values->email)
                    ->setHTMLBody($latte->renderToString(substr(__DIR__, 0, -10) . '/templates/Members/components/member-new-email.latte', $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
        } else {
            $this->flashMessage("Heslo uživatele je $pwd", 'note');
        }

        $this->redirect(":Admin:Members:edit", array("id" => $userId));
    }

    /**
     * User delete
     */
    function handleDelete($id)
    {
        $member = $this->database->table("users")->get($id);

        if ($member->username == 'admin') {
            $this->flashMessage('Nemůžete smazat účet administratora', 'error');
            $this->redirect(":Admin:Members:default");
        } elseif ($member->id == $this->user->getId()) {
            $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
            $this->redirect(":Admin:Members:default");
        }

        $this->database->table("users")->get($id)->delete();

        $this->redirect(":Admin:Members:default");
    }

    /**
     * Edit user password
     */
    function editPersonalSucceeded($cols)
    {
        setcookie("language", filter_input(INPUT_POST, "language"), time() + 60 * 60 * 24 * 180, "/");

        $params = array(
            "url" => _CALSET_PATHS_URI . '/usermanager/user.detail.html',
            "querystring" => array(
                "idf" => filter_input(INPUT_COOKIE, "auser")
            )
        );

        return $params;
    }

    public function renderDefault()
    {
        $this->template->members = $this->database->table("users");
    }

    public function renderEdit()
    {
        $this->template->members = $this->database->table("users")->get($this->getParameter("id"));
    }

}
