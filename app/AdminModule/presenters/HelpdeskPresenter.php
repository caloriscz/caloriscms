<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Helpdesk presenter.
 */
class HelpdeskPresenter extends BasePresenter
{
    protected function createComponentSendTestMail()
    {
        $control = new \Caloriscz\Helpdesk\SendTestMailControl($this->database);
        return $control;
    }

    protected function createComponentEditContactForm()
    {
        $control = new \Caloriscz\Helpdesk\EditContactFormControl($this->database);
        return $control;
    }

    protected function createComponentEditMailTemplate()
    {
        $control = new \Caloriscz\Helpdesk\EditMailTemplateControl($this->database);
        return $control;
    }

    function handleDelete($id)
    {
        $this->database->table("helpdesk_messages")->get($id)->delete();

        $this->redirect(this, array("id" => $this->getParameter("helpdesk")));
    }

    /**
     * Delete post
     */
    function handleDeleteTemplate($id)
    {
        $this->database->table("helpdesk_emails")->get($id)->delete();

        $this->redirect(":Admin:Helpdesk:default", array("id" => $this->getParameter("helpdesk")));
    }

    public function renderDefault()
    {
        $this->template->helpdesk = $this->database->table("helpdesk");
        $this->template->templates = $this->database->table("helpdesk_emails")->where("helpdesk_id", $this->getParameter("id"));

        if (!$this->getParameter("id")) {
            $helpdeskId = null;
        } else {
            $helpdeskId = $this->getParameter('id');
        }

        $messages = $this->database->table("helpdesk_messages")->where(array("helpdesk_id" => $helpdeskId))
            ->order("date_created DESC");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($messages->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->paginator = $paginator;
        $this->template->messages = $messages->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();


    }


    public function renderDetail()
    {
        $this->template->helpdesk = $this->database->table("helpdesk")->get($this->getParameter("id"));
        $this->template->message = $this->database->table("helpdesk_messages")->get($this->getParameter("id"));
    }
}
