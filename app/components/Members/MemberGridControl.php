<?php

namespace Caloriscz\Members;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class MemberGridControl extends Control
{

    /** @var Context*/
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentMemberGrid($name)
    {

        $grid = new DataGrid($this, $name);

        if ($this->getPresenter()->id === null) {
            $contacts = $this->database->table('users');
        } else {
            $contacts = $this->database->table('users')->where('users_categories_id', $this->getParameter('id'));
        }

        try {
            $grid->setTranslator($this->getPresenter()->translator);
            $grid->setDataSource($contacts);
            $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];

            $grid->addColumnLink('name', 'dictionary.main.Title')
                ->setRenderer(function ($item) {
                    $url = Html::el('a')->href($this->getPresenter()->link('edit', array("id" => $item->id)))
                        ->setText($item->username);
                    return $url;
                })
                ->setSortable();
            $grid->addColumnText('email', $this->getPresenter()->translator->translate('dictionary.main.Email'))
                ->setSortable();
            $grid->addColumnText('state', $this->getPresenter()->translator->translate('dictionary.main.State'))->setRenderer(function ($item) {
                $text = 'dictionary.main.disabled';

                if ($item->date_created === 1) {
                    $text = 'dictionary.main.enabled';
                }

                return $this->getPresenter()->translator->translate($text);
            })
                ->setSortable();
            $grid->addColumnText('date_created', $this->getPresenter()->translator->translate('dictionary.main.Date'))
                ->setRenderer(function ($item) {
                    $date = date('j. n. Y', strtotime($item->date_created));

                    return $date;
                })
                ->setSortable();
        } catch (\Exception $e) {
            echo 'data error';
        }
    }

    /**
     * User delete
     */
    public function handleDelete($id)
    {
        if (!$this->getPresenter()->template->member->users_roles->members) {
            $this->flashMessage($this->getPresenter()->translator->translate('messages.members.PermissionDenied'), 'error');
            $this->redirect('this', ['id' => null]);
        }

        for ($a = 0; $a < count($id); $a++) {
            $member = $this->database->table('users')->get($id[$a]);

            if ($member->username === 'admin') {
                $this->flashMessage('Nemůžete smazat účet administratora', 'error');
                $this->redirect(':Admin:Members:default', array('id' => null));
            } elseif ($member->id === $this->getPresenter()->user->getId()) {
                $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
                $this->redirect(':Admin:Members:default', array('id' => null));
            }

            $this->database->table('users')->get($id[$a])->delete();
        }

        $this->redirect('this', ['id' => null]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/MemberGridControl.latte');
        $this->template->render();
    }

}