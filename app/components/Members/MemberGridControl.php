<?php

namespace Caloriscz\Members;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class MemberGridControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    /**
     * User delete
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id): void
    {
        if (!$this->getPresenter()->template->member->users_roles->members) {
            $this->flashMessage('Nemáte oprávnění', 'error');
            $this->redirect('this', ['id' => null]);
        }

        $member = $this->database->table('users')->get($id);

        if ($member !== null) {
            if ($member->username === 'admin') {
                $this->flashMessage('Nemůžete smazat účet administratora', 'error');
                $this->redirect(':Admin:Members:default', ['id' => null]);
            } elseif ($member->id === $this->getPresenter()->user->getId()) {


                $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
                $this->redirect(':Admin:Members:default', ['id' => null]);
            }


            $this->database->table('users')->get($id)->delete();
        }

        $this->redirect('this', ['id' => null]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();


        $users = $this->database->table('users');

        $paginator = new Paginator();
        $paginator->setItemCount($users->count('*') ?? 1);
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $template->userList = $users->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->presenter->getParameters();

        $this->template->setFile(__DIR__ . '/MemberGridControl.latte');
        $this->template->render();
    }

}