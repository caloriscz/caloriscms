<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class MemberGridControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentMemberGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->presenter->id == null) {
            $contacts = $this->database->table("users");
        } else {
            $contacts = $this->database->table("users")->where("categories_id", $this->id);
        }
        $grid->setTranslator($this->presenter->translator);
        $grid->setDataSource($contacts);
        $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'dictionary.main.Title')
            ->setRenderer(function ($item) {
                $url = \Nette\Utils\Html::el('a')->href($this->presenter->link('edit', array("id" => $item->id)))
                    ->setText($item->username);
                return $url;
            })
            ->setSortable();
        $grid->addColumnText('email', $this->presenter->translator->translate('dictionary.main.Email'))
            ->setSortable();
        $grid->addColumnText('state', $this->presenter->translator->translate('dictionary.main.State'))->setRenderer(function ($item) {
            if ($item->date_created == 1) {
                $text = 'dictionary.main.enabled';
            } else {
                $text = 'dictionary.main.disabled';
            }
            return $this->presenter->translator->translate($text);
        })
            ->setSortable();
        $grid->addColumnText('date_created', $this->presenter->translator->translate('dictionary.main.Date'))
            ->setRenderer(function ($item) {
                $date = date("j. n. Y", strtotime($item->date_created));

                return $date;
            })
            ->setSortable();

        //$grid->setTranslator($this->translator);
    }

    /**
     * User delete
     */
    function handleDelete($id)
    {
        if (!$this->presenter->template->member->users_roles->members_delete) {
            $this->flashMessage($this->presenter->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->redirect(this, array("id" => null));
        }

        for ($a = 0; $a < count($id); $a++) {
            $member = $this->database->table("users")->get($id[$a]);

            if ($member->username == 'admin') {
                $this->flashMessage('Nemůžete smazat účet administratora', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            } elseif ($member->id == $this->presenter->user->getId()) {
                $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            }

            $this->database->table("users")->get($id[$a])->delete();
        }

        $this->redirect(this, array("id" => null));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/MemberGridControl.latte');

        $template->render();
    }

}