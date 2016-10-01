<?php
namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class SignInControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSignInForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("type");
        $form->addText("username", 'dictionary.main.User')
            ->setRequired('Vložte uživatelské jméno.');

        $form->addPassword("password", 'dictionary.main.Password')
            ->setRequired('Vložte heslo.');

        $form->addSubmit("send", 'dictionary.main.login')
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
            $this->presenter->flashMessage("Musíte nejdříve ověřit váš účet", 'error');
            $this->presenter->redirect(':Front:Sign:in');
        }

        try {
            $this->presenter->getUser()->login($values->username, $values->password);
            $newid = session_id();

            if ($this->presenter->template->settings['store:enabled']) {
                $this->database->table("orders")->where(array("uid" => $oldid))->update(array("uid" => $newid));
            }

            if ($form->values->type == 'admin') {
                $role = $this->presenter->user->getRoles();
                $roleCheck = $this->database->table("users_roles")->get($role[0]);

                if ($roleCheck->admin_access == 0) {
                    $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.no-access'), "error");
                    $this->presenter->redirect(':Admin:Sign:in');
                } else {
                    $this->database->table("users")->get($this->presenter->user->getId())->update(array("date_visited" => date("Y-m-d H:i:s")));
                }

                $typeUrl = 'Admin';
            } else {
                $typeUrl = 'Front';
            }


            $this->database->table("users")->get($this->presenter->user->getId())->update(array(
                "date_visited" => date("Y-m-d H:i:s"),
                "login_success" => new \Nette\Database\SqlLiteral("login_success + 1")
            ));

            $this->presenter->redirect(':' . $typeUrl . ':Homepage:default');
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->database->table("users")->where(array("username" => $values->username))->update(array(
                "login_error" => new \Nette\Database\SqlLiteral("login_error + 1")
            ));

            $this->presenter->flashMessage("Nesprávné heslo", 'error');
            $this->presenter->redirect(':' . $typeUrl . ':Sign:in');
        }
    }

    public function render($type = 'front')
    {
        $template = $this->template;
        $template->type = $type;
        $template->setFile(__DIR__ . '/SignInControl.latte');

        $template->render();
    }

}