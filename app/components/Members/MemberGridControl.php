<?php

namespace Caloriscz\Members;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\InvalidStateException;
use Nette\Utils\Html;
use Nette\Utils\Paginator;
use Ublaboo\DataGrid\DataGrid;

class MemberGridControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }
    /**
     * User delete
     */
    public function handleDelete($id): void
    {
        if (!$this->getPresenter()->template->member->users_roles->members) {
            $this->flashMessage($this->getPresenter()->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->redirect('this', ['id' => null]);
        }

        $member = $this->database->table('users')->get($id);

        if ($member->username === 'admin') {
            $this->flashMessage('Nemůžete smazat účet administratora', 'error');
            $this->redirect(':Admin:Members:default', ['id' => null]);
        } elseif ($member->id === $this->getPresenter()->user->getId()) {
            $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
            $this->redirect(':Admin:Members:default', ['id' => null]);
        }

        $this->database->table('users')->get($id)->delete();

        $this->redirect('this', ['id' => null]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();


        $users = $this->database->table('users');

        $paginator = new Paginator();
        $paginator->setItemCount($users->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page'));

        $template->userList = $users->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->presenter->getParameters();

        $this->template->setFile(__DIR__ . '/MemberGridControl.latte');
        $this->template->render();
    }

}