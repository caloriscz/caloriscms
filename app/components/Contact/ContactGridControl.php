<?php

namespace Caloriscz\Contact;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class ContactGridControl extends Control
{

    /** @var Explorer */
    public $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    /**
     * Delete contact with all other tables and related page
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id): void
    {
        $this->database->table('contacts')->get($id)->delete();
        $this->presenter->redirect('this');
    }

    public function render(): void
    {
        $template = $this->getTemplate();

        $contacts = $this->database->table('contacts');

        $paginator = new Paginator();
        $paginator->setItemCount($contacts->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $template->contacts = $contacts->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->presenter->getParameters();

        $template->setFile(__DIR__ . '/ContactGridControl.latte');

        $template->render();
    }

}