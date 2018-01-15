<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Members\EditMemberControl;
use Caloriscz\Members\InsertContactForMemberControl;
use Caloriscz\Members\InsertMemberControl;
use Caloriscz\Members\MemberCategoriesControl;
use Caloriscz\Members\MemberGridControl;
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
        $control->onSave[] = function ($contactId, $pwd) {
            if ($pwd) {
                $this->flashMessage($pwd, 'success');
            }

            $this->redirect(this, array('id' => $contactId, 'pdd' => $pwd));
        };

        return $control;
    }

    protected function createComponentEditMember()
    {
        return new EditMemberControl($this->database);
    }

    protected function createComponentInsertMember()
    {
        $control = new InsertMemberControl($this->database);
        $control->onSave[] = function ($message, $userId) {
            if ($message) {
                $this->flashMessage($this->translator->translate($message), 'error');
                $code = ':Admin:Members:default';
            } else {
                $code = ':Admin:Members:edit';
            }

            $this->redirect($code, array('id' => $userId));
        };

        return $control;
    }

    protected function createComponentInsertContactForMember()
    {
        return new InsertContactForMemberControl($this->database);
    }
    
    protected function createComponentMemberGrid()
    {
        return new MemberGridControl($this->database);
    }
    
    protected function createComponentMemberCategories()
    {
        return new MemberCategoriesControl($this->database);
    }

    /**
     * Contact for user delete
     */
    public function handleDeleteContact($id)
    {
        if (!$this->template->member->users_roles->members_edit) {
            $this->flashMessage($this->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->redirect(':Admin:Members:edit', ['id' => $this->getParameter('contact')]);
        }

        try {
            $this->database->table('contacts')->get($id)->delete();
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 15) == 'SQLSTATE[23000]') {
                $message = ': Kontkat je potřebný v Objednávkách';
            }

            $this->flashMessage($this->translator->translate('messages.sign.CannotBeDeleted') . ': ' . $message, 'error');
        }

        $this->redirect(':Admin:Members:edit', array('id' => $this->getParameter('contact')));
    }

    public function renderEdit()
    {
        $this->template->members = $this->database->table('users')->get($this->getParameter('id'));
        $this->template->role = $this->user->getRoles();
    }

}
