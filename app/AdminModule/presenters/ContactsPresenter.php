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
        return new InsertContactControl($this->database);
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
     * Delete categories
     * @param $id
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteCategory($id): void
    {
        $category = new Model\Category($this->database);

        $this->database->table('contacts_categories')->where('id', $category->getSubIds($id))->delete();

        $this->redirect(':Admin:Categories:default');
    }

    /**
     * @param $id
     */
    public function handleDelete($id): void
    {
        $this->database->table('contacts_categories');
    }

    public function renderDefault(): void
    {
        $contactsDb = $this->database->table('contacts')->order('name');

        $paginator = new Nette\Utils\Paginator();
        $paginator->setItemCount($contactsDb->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page'));

        $this->template->args = $this->getParameters();
        $this->template->paginator = $paginator;
        $this->template->contacts = $contactsDb->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->menu = $this->database->table('contacts_categories')->where('parent_id', null);
    }

    public function renderDetailOpeningHours(): void
    {
        $this->template->hours = $this->database->table('contacts_openinghours')->where('contacts_id', $this->getParameter('id'));
    }

    public function renderCategories(): void
    {
        $categoryId = null;

        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table('contacts_categories')->where('parent_id', $categoryId)
            ->order('sorted DESC');
    }

    public function renderCategoriesDetail(): void
    {
        $this->template->menu = $this->database->table('contacts_categories')->get($this->getParameter('id'));
    }

}
