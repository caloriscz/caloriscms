<?php

namespace Caloriscz\Settings;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class BlackListControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete(int $id): void
    {
        $this->database->table('blacklist')->get($id)->delete();
        $this->presenter->redirect('this', ['id' => $this->presenter->getParameter('id')]);
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    /**
     *
     * @param int $id
     * @param int $type Insert blaclist word or sentence
     */
    public function render(int $id = null, int $type = null): void
    {
        $this->template->type = $type;
        $blacklist = $this->database->table('blacklist')->order('title');

        $paginator = new Paginator();
        $paginator->setItemCount($blacklist->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $this->template->blacklist = $blacklist->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();


        $this->template->setFile(__DIR__ . '/BlackListControl.latte');
        $this->template->render();
    }
}