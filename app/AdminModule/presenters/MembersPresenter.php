<?php

namespace App\AdminModule\Presenters;

use App\Forms\Contacts\SendLoginControl;
use App\Forms\Members\EditMemberControl;
use App\Forms\Members\InsertContactForMemberControl;
use App\Forms\Members\InsertMemberControl;
use Caloriscz\Members\MemberGridControl;
use Caloriscz\Members\MemberCategoriesControl;

/**
 * Members (aka Users) presenter
 * @package App\AdminModule\Presenters
 */
class MembersPresenter extends BasePresenter
{
    protected function createComponentSendLogin()
    {
        $control = new SendLoginControl($this->database);
        $control->onSave[] = function ($contactId, $pwd) {
            if ($pwd) {
                $this->flashMessage($pwd, 'success');
            }

            $this->redirect('this', ['id' => $contactId, 'pdd' => $pwd]);
        };

        return $control;
    }

    protected function createComponentEditMember(): EditMemberControl
    {
        return new EditMemberControl($this->database);
    }

    protected function createComponentInsertMember(): InsertMemberControl
    {
        $control = new InsertMemberControl($this->database);
        $control->onSave[] = function ($message, $userId) {
            if ($message) {
                $this->flashMessage($this->translator->translate($message), 'error');
                $code = ':Admin:Members:default';
            } else {
                $code = ':Admin:Members:edit';
            }

            $this->redirect($code, ['id' => $userId]);
        };

        return $control;
    }

    protected function createComponentInsertContactForMember(): InsertContactForMemberControl
    {
        return new InsertContactForMemberControl($this->database);
    }
    
    protected function createComponentMemberGrid(): MemberGridControl
    {
        return new MemberGridControl($this->database);
    }

    /**
     * Contact for user delete
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDeleteContact($id): void
    {
        if (!$this->template->member->users_roles->members) {
            $this->flashMessage($this->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->redirect(':Admin:Members:edit', ['id' => $this->getParameter('contact')]);
        }

        $this->redirect(':Admin:Members:edit', ['id' => $this->getParameter('contact')]);
    }

    public function renderEdit(): void
    {
        $this->template->members = $this->database->table('users')->get($this->getParameter('id'));
        $this->template->role = $this->user->getRoles();
    }

}
