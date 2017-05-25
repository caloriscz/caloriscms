<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class EditMemberControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit user data
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $user = $this->database->table("users")->get($this->presenter->getParameter("id"));

        $form->addHidden('id');
        $form->addRadioList("sex", "Pohlaví", array(1 => ' žena', 2 => ' muž'));
        $form->addRadioList("newsletter", "Odebírat newsletter", array(1 => ' ano', 2 => ' ne'));
        $form->addRadioList("state", "Stav účtu", array(1 => ' povolen', 2 => ' blokován'));

        $roles = $this->database->table("users_roles")->fetchPairs("id", "title");

        if ($this->template->member->username == 'admin') {
            $form->addSelect("role", "Role", $roles)
                ->setAttribute("class", "form-control");
        }

        if ($this->presenter->template->settings['members:groups:enabled']) {
            $groups = $this->database->table("users_categories")->fetchPairs("id", "title");

            $form->addSelect("group", "Skupina", $groups)
                ->setAttribute("class", "form-control");
        }

        $arr = array(
            "id" => $this->presenter->getParameter("id"),
            "sex" => $user->sex,
            "newsletter" => $user->newsletter,
            "state" => $user->state,
            "role" => $user->role,
            "group" => $user->categories_id,
        );

        $form->setDefaults(array_filter($arr));

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->onValidate[] = $this->editFormValidated;

        return $form;
    }

    function editFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!$this->presenter->template->member->users_roles->members_edit) {
            $this->presenter->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->presenter->redirect(this, array("id" => $form->values->id));
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $arr = array(
            "sex" => $form->values->sex,
            "newsletter" => $form->values->newsletter,
            "state" => $form->values->state,
        );

        if ($this->presenter->template->member->username) {
            $arr["users_roles_id"] = $form->values->role;
        }

        if ($this->presenter->template->settings['members:groups:enabled']) {
            $arr["categories_id"] = $form->values->group;
        }

        $this->database->table("users")->where(array(
            "id" => $form->values->id,
        ))->update($arr);

        $this->presenter->redirect(this, array("" => $form->values->id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditMemberControl.latte');

        $template->render();
    }

}
