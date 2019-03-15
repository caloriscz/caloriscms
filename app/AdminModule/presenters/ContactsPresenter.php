<?php

namespace App\AdminModule\Presenters;

use App\Forms\Contacts\EditCategoryControl;
use App\Forms\Contacts\EditContactControl;
use App\Forms\Contacts\InsertCategoryControl;
use App\Forms\Contacts\InsertCommunicationControl;
use App\Forms\Contacts\InsertContactCategoryControl;
use App\Forms\Contacts\InsertContactControl;
use App\Forms\Contacts\InsertHourControl;
use App\Forms\Contacts\LoadVatControl;
use Apps\Forms\Profile\EditControl;
use Caloriscz\Contact\CommunicationGridControl;
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
                $this->flashMessage($this->translator->translate('messages.sign.fillInEmail'), 'error');
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

    protected function createComponentInsertCommunication(): InsertCommunicationControl
    {
        return new InsertCommunicationControl($this->database);
    }

    protected function createComponentLoadVat(): LoadVatControl
    {
        return new LoadVatControl($this->database);
    }

    protected function createComponentContactGrid(): ContactGridControl
    {
        return new ContactGridControl($this->database);
    }

    protected function createComponentCommunicationGrid(): CommunicationGridControl
    {
        return new CommunicationGridControl($this->database);
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
     * @param $sorted
     * @throws Nette\Application\AbortException
     */
    public function handleUp($id, $sorted): void
    {
        $sortDb = $this->database->table('contacts_categories')->where([
            'sorted > ?' => $sorted,
            'parent_id' => $this->getParameter('category')
        ])->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('contacts_categories')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('contacts_categories')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Contacts:default', ['id' => null]);
    }

    /**
     * @param $id
     * @param $sorted
     * @param $category
     * @throws Nette\Application\AbortException
     */
    public function handleDown($id, $sorted, $category): void
    {
        $sortDb = $this->database->table('contacts_categories')->where([
            'sorted < ?' => $sorted,
            'parent_id' => $category
        ])->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('contacts_categories')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('contacts_categories')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->presenter->redirect('this', ['id' => null]);
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

    public function renderCommunications(): void
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->communications = $this->database->table('contacts_communications')->where([
            'contacts_id' => $this->getParameter('id')
        ]);
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
