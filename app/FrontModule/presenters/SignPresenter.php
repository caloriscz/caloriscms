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

    protected function createComponentLostPass()
    {
        $control = new \Caloriscz\Sign\LostPassControl($this->database);
        return $control;
    }

    protected function createComponentResetPass()
    {
        $control = new \Caloriscz\Sign\ResetPassControl($this->database);
        return $control;
    }

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

    protected function createComponentSignUp()
    {
        $control = new \Caloriscz\Sign\SignUpControl($this->database);
        return $control;
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