<?php

namespace App\AdminModule\Presenters;

use App\Forms\Helpdesk\EditHelpdeskEmailSettingsControl;
use App\Forms\Helpdesk\EditMailTemplateControl;
use Nette\Application\AbortException;
use Nette\Utils\Paginator;

/**
 * Helpdesk presenter.
 */
class HelpdeskPresenter extends BasePresenter
{

    protected function createComponentEditHelpdeskEmailSettings(): EditHelpdeskEmailSettingsControl
    {
        $control = new EditHelpdeskEmailSettingsControl($this->database);
        $control->onSave[] = function ($helpdeskId) {
            $this->redirect('this', ['id' => $helpdeskId]);
        };

        return $control;
    }

    protected function createComponentEditMailTemplate(): EditMailTemplateControl
    {
        return new EditMailTemplateControl($this->database);
    }

    /**
     * Delete message
     * @param $identifier
     * @throws AbortException
     */
    public function handleDelete($identifier): void
    {
        $message = $this->database->table('helpdesk_messages')->get($identifier);
        if ($message === null) {
            $this->error('Record not found.', 404);
        }
        $helpdeskId = $message->helpdesk_id;
        $message->delete();

        // To the message list of its form, also from the message's own page, which no longer exists.
        $this->redirect(':Admin:Helpdesk:emails', ['id' => $helpdeskId]);
    }

    /**
     * Delete template
     * @param $identifier
     * @throws AbortException
     */
    public function handleDeleteTemplate($identifier): void
    {
        $this->database->table('helpdesk')->get($identifier)->delete();

        $this->redirect(':Admin:Helpdesk:default', ['id' => $this->getParameter('helpdesk')]);
    }

    public function renderDefault(): void
    {
        $this->template->helpdesk = $this->database->table('helpdesk')->order('title');
    }

    public function actionEmails(): void
    {
        // Without an id the page still lists messages whose form was deleted
        // (helpdesk_id is set to NULL), with no form to edit.
        $this->template->templates = $this->getParameter('id') === null ? null : $this->requireRecord('helpdesk');
    }

    public function renderEmails(): void
    {
        $helpdeskId = $this->template->templates ? $this->template->templates->id : null;

        $messages = $this->database->table('helpdesk_messages')->where(['helpdesk_id' => $helpdeskId])
            ->order('date_created DESC');

        $paginator = new Paginator();
        $paginator->setItemCount($messages->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page') ?? 1);

        $this->template->paginator = $paginator;
        $this->template->messages = $messages->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();
    }

    public function actionDetail(): void
    {
        // The id is a message id; the page shows only the message.
        $this->template->message = $this->requireRecord('helpdesk_messages');
    }
}
