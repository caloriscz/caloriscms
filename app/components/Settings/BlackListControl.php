<?php

namespace Caloriscz\Settings;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Paginator;

class BlackListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id): void
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
     * @param type $id
     * @param type $type Insert blaclist word or sentence
     */
    public function render($id = null, $type = null): void
    {
        $this->template->type = $type;
        $blacklist = $this->database->table('blacklist')->order('title');

        $paginator = new Paginator();
        $paginator->setItemCount($blacklist->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page'));

        $this->template->blacklist = $blacklist->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();


        $this->template->setFile(__DIR__ . '/BlackListControl.latte');
        $this->template->render();
    }
}