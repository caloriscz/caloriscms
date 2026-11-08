<?php

namespace App\AdminModule\Presenters;

use App\Forms\Contacts\SendLoginControl;
use App\Forms\Members\EditMemberControl;
use App\Forms\Members\InsertContactForMemberControl;
use App\Forms\Members\InsertMemberControl;
use Caloriscz\Members\MemberGridControl;

/**
 * Members (aka Users) presenter
 * @package App\AdminModule\Presenters
 */
class MembersPresenter extends BasePresenter
{
    protected function createComponentSendLogin(): SendLoginControl
    {
        $control = new SendLoginControl($this->database);
        $control->onSave[] = function ($contactId) {
            $this->flashMessage('Odkaz pro nastavení hesla byl odeslán.', 'success');
            $this->redirect('this', ['id' => $contactId]);
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
                $this->flashMessage($message, 'error');
                $this->redirect(':Admin:Members:edit');
            }

            $this->redirect(':Admin:Members:edit', ['id' => $userId]);
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
     * Delete a contact assigned to this member, as the button did until 2018
     * (owner decision of 2026-11-08). Only that member's contacts can be deleted here.
     * @param $contactId
     * @throws \Nette\Application\AbortException
     */
    public function handleDeleteContact($contactId): void
    {
        if (!$this->template->memberRole || !$this->template->memberRole->members) {
            $this->flashMessage('Nemáte oprávnění', 'error');
            $this->redirect('this');
        }

        $contact = $this->database->table('contacts')
            ->where(['id' => (int) $contactId, 'users_id' => $this->template->members->id])
            ->fetch();
        if (!$contact) {
            $this->error('Record not found.', 404);
        }
        // Its opening hours go with it (contacts_openinghours cascades), as in the contact list.
        $contact->delete();

        $this->flashMessage('Kontakt byl smazán.', 'success');
        $this->redirect('this');
    }

    public function actionEdit(): void
    {
        $this->template->members = $this->requireRecord('users');
        $this->template->role = $this->user->getRoles();
    }

}
