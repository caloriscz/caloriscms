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
    public function handleDelete($identifier)
    {
        $this->database->table('helpdesk_messages')->get($identifier)->delete();

        $this->redirect('this', ['id' => $this->getParameter('helpdesk')]);
    }

    /**
     * Delete template
     * @param $identifier
     * @throws AbortException
     */
    public function handleDeleteTemplate($identifier)
    {
        $this->database->table('helpdesk_emails')->get($identifier)->delete();

        $this->redirect(':Admin:Helpdesk:default', ['id' => $this->getParameter('helpdesk')]);
    }

    public function renderDefault()
    {
        $this->template->helpdesk = $this->database->table('helpdesk');
        $this->template->templates = $this->database->table('helpdesk_emails')->where('helpdesk_id', $this->getParameter('id'));

        if (!$this->getParameter('id')) {
            $helpdeskId = null;
        } else {
            $helpdeskId = $this->getParameter('id');
        }

        $messages = $this->database->table('helpdesk_messages')->where(['helpdesk_id' => $helpdeskId])
            ->order('date_created DESC');

        $paginator = new Paginator();
        $paginator->setItemCount($messages->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page'));

        $this->template->paginator = $paginator;
        $this->template->messages = $messages->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();
    }

    public function renderDetail()
    {
        $this->template->helpdesk = $this->database->table('helpdesk')->get($this->getParameter('id'));
        $this->template->message = $this->database->table('helpdesk_messages')->get($this->getParameter('id'));
    }
}
