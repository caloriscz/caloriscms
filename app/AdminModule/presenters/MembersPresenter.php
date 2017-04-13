<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Members\MemberCategoriesControl;
use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class MembersPresenter extends BasePresenter
{

    protected function createComponentSendLogin()
    {
        $control = new \Caloriscz\Contacts\ContactForms\SendLoginControl($this->database);
        return $control;
    }

    protected function createComponentEditMember()
    {
        $control = new \Caloriscz\Members\EditMemberControl($this->database);
        return $control;
    }

    protected function createComponentInsertMember()
    {
        $control = new \Caloriscz\Members\InsertMemberControl($this->database);
        return $control;
    }

    protected function createComponentInsertContactForMember()
    {
        $control = new \Caloriscz\Members\InsertContactForMemberControl($this->database);
        return $control;
    }
    
    protected function createComponentMemberGrid()
    {
        $control = new \Caloriscz\Members\MemberGridControl($this->database);
        return $control;
    }
    
    protected function createComponentMemberCategories()
    {
        $control = new MemberCategoriesControl($this->database);
        return $control;
    }

    /**
     * Contact for user delete
     */
    function handleDeleteContact($id)
    {
        if (!$this->template->member->users_roles->members_edit) {
            $this->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->redirect(":Admin:Members:edit", array("id" => $this->getParameter("contact")));
        }

        try {
            $this->database->table("contacts")->get($id)->delete();
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 15) == 'SQLSTATE[23000]') {
                $message = ': Kontkat je potřebný v Objednávkách';
            }

            $this->flashMessage($this->translator->translate('messages.sign.CannotBeDeleted') . ': ' . $message, "error");
        }

        $this->redirect(":Admin:Members:edit", array("id" => $this->getParameter("contact")));
    }

    public function renderEdit()
    {
        $this->template->members = $this->database->table("users")->get($this->getParameter("id"));
        $this->template->role = $this->user->getRoles();
    }

}
