<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class InsertMemberControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert new user
     */
    function createComponentInsertForm()
    {
        $roles = $this->database->table("users_roles")->fetchPairs("id", "title");
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("username", "dictionary.main.Member")
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3);
        $form->addText("email", "dictionary.main.Email");

        if ($this->presenter->template->member->username == 'admin') {
            $form->addSelect("role", "dictionary.main.Role", $roles)
                ->setAttribute("class", "form-control");
        }
        $form->addCheckbox("sendmail", "messages.members.sendLoginEmail")
            ->setValue(1);

        $form->addSubmit('submitm', 'dictionary.main.Create')->setAttribute("class", "btn btn-success");
        $form->onSuccess[] = $this->insertFormSucceeded;
        $form->onValidate[] = $this->insertFormValidated;

        return $form;
    }

    function insertFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        $member = new \App\Model\MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        if (!$this->presenter->template->member->users_roles->members_create) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->presenter->redirect(":Admin:Members:default", array("id" => null));
        }

        if (\Nette\Utils\Validators::isEmail($form->values->email) == false) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.invalidEmailFormat"), 'error');
            $this->presenter->redirect(":Admin:Members:default", array("id" => null));
        } elseif ($emailExists > 0) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.emailAlreadyExists"), 'error');
            $this->presenter->redirect(":Admin:Members:default", array("id" => null));
        } elseif ($userExists > 0) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.memberAlreadyExists"), 'error');
            $this->presenter->redirect(":Admin:Members:default", array("id" => null));
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $pwd = \Nette\Utils\Random::generate(10);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);

        $userId = $this->database->table("users")
            ->insert(array(
                "email" => $form->values->email,
                "username" => $form->values->username,
                "password" => $pwdEncrypted,
                "date_created" => date("Y-m-d H:i:s"),
                "users_roles_id" => $form->values->role,
                "state" => 1,
            ));

        if ($form->values->sendmail) {
            $latte = new \Latte\Engine;
            $latte->setLoader(new \Latte\Loaders\StringLoader());
            $params = array(
                'username' => $form->values->username,
                'password' => $pwd,
                'settings' => $this->presenter->template->settings,
            );

            $helpdesk = $this->database->table("helpdesk")->get(5);
            $helpdesk_new = $helpdesk->related("helpdesk_emails", "helpdesk_id")->get(9);
            $helpdesk_new_template = $latte->renderToString($helpdesk_new->body, $params);

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->presenter->template->settings["site:title"] . ' <' . $this->presenter->template->settings["contacts:email:hq"] . '>')
                ->addTo($form->values->email)
                ->setHTMLBody($helpdesk_new_template);

            $this->presenter->mailer->send($mail);
        } else {
            $this->presenter->flashMessage("Heslo uživatele je $pwd", 'note');
        }

        $this->presenter->redirect(":Admin:Members:edit", array("id" => $userId));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertMemberControl.latte');

        $template->render();
    }

}
