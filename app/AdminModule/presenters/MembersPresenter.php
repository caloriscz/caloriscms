<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class MembersPresenter extends BasePresenter
{

    protected function createComponentSendLogin()
    {
        $control = new \Caloriscz\Contacts\ContactForms\SendLoginControl($this->database);
        return $control;
    }

    protected function createComponentEditMember()
    {
        $control = new \Caloriscz\Members\EditMemberControl($this->database);
        return $control;
    }

    protected function createComponentInsertMember()
    {
        $control = new \Caloriscz\Members\InsertMemberControl($this->database);
        return $control;
    }

    protected function createComponentInsertContactForMember()
    {
        $control = new \Caloriscz\Members\InsertContactForMemberControl($this->database);
        return $control;
    }

    /**
     * User delete
     */
    function handleDelete($id)
    {
        if (!$this->template->member->users_roles->members_delete) {
            $this->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->redirect(":Admin:Members:default", array("id" => null));
        }

        for ($a = 0; $a < count($id); $a++) {
            $member = $this->database->table("users")->get($id[$a]);

            if ($member->username == 'admin') {
                $this->flashMessage('Nemůžete smazat účet administratora', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            } elseif ($member->id == $this->user->getId()) {
                $this->flashMessage('Nemůžete smazat vlastní účet', 'error');
                $this->redirect(":Admin:Members:default", array("id" => null));
            }

            $this->database->table("users")->get($id[$a])->delete();
        }

        $this->redirect(":Admin:Members:default", array("id" => null));
    }

    /**
     * Contact for user delete
     */
    function handleDeleteContact($id)
    {
        if (!$this->template->member->users_roles->members_edit) {
            $this->flashMessage($this->translator->translate("messages.members.PermissionDenied"), 'error');
            $this->redirect(":Admin:Members:edit", array("id" => $this->getParameter("contact")));
        }

        try {
            $this->database->table("contacts")->get($id)->delete();
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 15) == 'SQLSTATE[23000]') {
                $message = ': Kontkat je potřebný v Objednávkách';
            }

            $this->flashMessage($this->translator->translate('messages.sign.CannotBeDeleted') . ': ' . $message, "error");
        }

        $this->redirect(":Admin:Members:edit", array("id" => $this->getParameter("contact")));
    }

    protected function createComponentMembersGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->id == NULL) {
            $contacts = $this->database->table("users");
        } else {
            $contacts = $this->database->table("users")->where("categories_id", $this->id);
        }
        $grid->setTranslator($this->translator);
        $grid->setDataSource($contacts);
        $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'dictionary.main.Title')
            ->setRenderer(function ($item) {
                $url = Nette\Utils\Html::el('a')->href($this->link('edit', array("id" => $item->id)))
                    ->setText($item->username);
                return $url;
            })
            ->setSortable();
        $grid->addColumnText('email', $this->translator->translate('dictionary.main.Email'))
            ->setSortable();
        $grid->addColumnText('state', $this->translator->translate('dictionary.main.State'))->setRenderer(function ($item) {
            if ($item->date_created == 1) {
                $text = 'dictionary.main.enabled';
            } else {
                $text = 'dictionary.main.disabled';
            }
            return $this->translator->translate($text);
        })
            ->setSortable();
        $grid->addColumnText('date_created', $this->translator->translate('dictionary.main.Date'))
            ->setRenderer(function ($item) {
                $date = date("j. n. Y", strtotime($item->date_created));

                return $date;
            })
            ->setSortable();

        //$grid->setTranslator($this->translator);
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:members'];
    }

    public function renderEdit()
    {
        $this->template->members = $this->database->table("users")->get($this->getParameter("id"));
        $this->template->role = $this->user->getRoles();
    }

}
