<?php

namespace App\AdminModule\Presenters;

use App\Forms\Contacts\EditCategoryControl;
use App\Forms\Contacts\EditContactControl;
use App\Forms\Contacts\InsertCategoryControl;
use App\Forms\Contacts\InsertContactCategoryControl;
use App\Forms\Contacts\InsertContactControl;
use App\Forms\Contacts\InsertHourControl;
use Caloriscz\Contact\ContactGridControl;
use Nette,
    App\Model;

/**
 * Contacts presenter.
 * @package App\AdminModule\Presenters
 */
class ContactsPresenter extends BasePresenter
{

    /**
     * @return EditContactControl
     */
    protected function createComponentEditContact(): EditContactControl
    {
        $control = new EditContactControl($this->database);
        $control->onSave[] = function ($pages_id, $error = null) {
            if ($error === 1) {
                $this->flashMessage('Vyplňte e-mail', 'error');
            }

            $this->redirect('this', ['id' => $pages_id]);
        };

        return $control;
    }

    protected function createComponentInsertContact(): InsertContactControl
    {
        $control = new InsertContactControl($this->database);
        $control->onSave[] = function ($id) {
            $this->redirect('this', ['id' => $id]);
        };

        return $control;
    }

    protected function createComponentInsertHour(): InsertHourControl
    {
        return new InsertHourControl($this->database);
    }

    protected function createComponentContactGrid(): ContactGridControl
    {
        return new ContactGridControl($this->database);
    }

    protected function createComponentCategoryEdit(): EditCategoryControl
    {
        return new EditCategoryControl($this->database);
    }

    protected function createComponentCategoryInsert(): InsertCategoryControl
    {
        return new InsertCategoryControl($this->database);
    }

    protected function createComponentContactCategories(): InsertContactCategoryControl
    {
        return new InsertContactCategoryControl($this->database);
    }

    /**
     * Delete hour
     * @param $id
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteHour($id): void
    {
        $this->database->table('contacts_openinghours')->get($this->getParameter('hour'))->delete();

        $this->redirect(':Admin:Contacts:detailOpeningHours', ['id' => $id]);
    }

    /**
     * Delete an empty contact category. contacts.contacts_categories_id cascades
     * on delete, so a category that still has contacts would take them with it;
     * one with subcategories would leave them without a parent.
     * @param $categoryId
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteCategory($categoryId): void
    {
        $category = $this->database->table('contacts_categories')->get((int) $categoryId);
        if ($category === null) {
            $this->error('Record not found.', 404);
        }

        $contacts = $this->database->table('contacts')->where('contacts_categories_id', $category->id)->count('*');
        $children = $this->database->table('contacts_categories')->where('parent_id', $category->id)->count('*');
        if ($contacts > 0) {
            $this->flashMessage(sprintf('Kategorii „%s“ nelze smazat, obsahuje kontakty (%d). Přesuňte je nejdřív do jiné kategorie.',
                $category->title, $contacts), 'error');
        } elseif ($children > 0) {
            $this->flashMessage(sprintf('Kategorii „%s“ nelze smazat, má podkategorie. Smažte nebo přesuňte je nejdřív.',
                $category->title), 'error');
        } else {
            $category->delete();
            $this->flashMessage(sprintf('Kategorie „%s“ byla smazána.', $category->title), 'success');
        }

        $this->redirect('this');
    }

    public function renderDefault(): void
    {
        $contactsDb = $this->database->table('contacts')->order('name');

        $paginator = new Nette\Utils\Paginator();
        $paginator->setItemCount($contactsDb->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page') ?? 1);

        $this->template->args = $this->getParameters();
        $this->template->paginator = $paginator;
        $this->template->contacts = $contactsDb->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->menu = $this->database->table('contacts_categories')->where('parent_id', null);
    }

    public function actionDetail(): void
    {
        $this->requireRecord('contacts');
    }

    public function actionDetailOpeningHours(): void
    {
        $contact = $this->requireRecord('contacts');
        $this->template->hours = $this->database->table('contacts_openinghours')->where('contacts_id', $contact->id);
    }

    public function renderCategories(): void
    {
        $categoryId = null;

        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        }

        $this->template->database = $this->database;
        // contacts_categories has no 'sorted' column (dropped with the 2019 sorting UI).
        $this->template->menu = $this->database->table('contacts_categories')->where('parent_id', $categoryId)
            ->order('title');
    }

    public function actionCategoryDetail(): void
    {
        $this->template->category = $this->requireRecord('contacts_categories');
    }

}
