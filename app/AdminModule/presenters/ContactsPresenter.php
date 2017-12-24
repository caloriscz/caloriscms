<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Categories\EditCategoryControl;
use Caloriscz\Categories\InsertCategoryControl;
use Caloriscz\Contact\CommunicationGridControl;
use Caloriscz\Contact\ContactGridControl;
use Caloriscz\Contacts\ContactForms\InsertCommunicationControl;
use Caloriscz\Contacts\ContactForms\InsertContactControl;
use Caloriscz\Contacts\ContactForms\InsertHourControl;
use Caloriscz\Contacts\ContactForms\LoadVatControl;
use Caloriscz\Menus\Admin\ContactCategoriesControl;
use Nette,
    App\Model;

/**
 * Contacts presenter.
 */
class ContactsPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
    }

    /** @var \Caloriscz\Contacts\ContactForms\IEditContactControlFactory @inject */
    public $editContactControlFactory;

    protected function createComponentEditContact()
    {
        $control = $this->editContactControlFactory->create();
        $control->onSave[] = function ($pages_id, $error = null) {
            if ($error === 1) {
                $this->flashMessage($this->translator->translate('messages.sign.fillInEmail'), 'error');
            }

            $this->redirect(this, array('id' => $pages_id));
        };

        return $control;
    }

    protected function createComponentInsertContact()
    {
        return new InsertContactControl($this->database);
    }

    protected function createComponentInsertHour()
    {
        return new InsertHourControl($this->database);
    }

    protected function createComponentInsertCommunication()
    {
        return new InsertCommunicationControl($this->database);
    }

    protected function createComponentLoadVat()
    {
        return new LoadVatControl($this->database);
    }

    protected function createComponentContactGrid()
    {
        return new ContactGridControl($this->database);
    }

    protected function createComponentCommunicationGrid()
    {
        return new CommunicationGridControl($this->database);
    }

    protected function createComponentCategoryEdit()
    {
        return new EditCategoryControl($this->database);
    }

    protected function createComponentCategoryInsert()
    {
        return new InsertCategoryControl($this->database);
    }

    protected function createComponentContactCategories()
    {
        return new ContactCategoriesControl($this->database);
    }

    /**
     * Delete hour
     */
    public function handleDeleteHour($id)
    {
        $this->database->table('contacts_openinghours')->get($this->getParameter('hour'))->delete();

        $this->redirect(':Admin:Contacts:detailOpeningHours', array('id' => $id));
    }

    /**
     * Delete categories
     */
    public function handleDeleteCategory($id)
    {
        $category = new Model\Category($this->database);

        $this->database->table('categories')->where('id', $category->getSubIds($id))->delete();

        $this->redirect(':Admin:Categories:default');
    }

    public function handleUpCategory($id, $sorted)
    {
        $sortDb = $this->database->table('categories')->where([
            'sorted > ?' => $sorted,
            'parent_id' => $this->getParameter('category')
        ])->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('categories')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('categories')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Categories:default', ['id' => null]);
    }

    public function handleDownCategory($id, $sorted, $category)
    {
        $sortDb = $this->database->table('contacts_categories')->where([
            'sorted < ?' => $sorted,
            'parent_id' => $category
        ])->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('contacts_categories')->where(array('id' => $id))->update(['sorted' => $sort->sorted]);
            $this->database->table('contacts_categories')->where(array('id' => $sort->id))->update(['sorted' => $sorted]);
        }

        $this->presenter->redirect(this, ['id' => null]);
    }

    public function renderDefault()
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

    public function renderDetailOpeningHours()
    {
        $this->template->hours = $this->database->table('contacts_openinghours')->where('contacts_id', $this->getParameter('id'));
    }

    public function renderCommunications()
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->communications = $this->database->table('contacts_communications')->where([
            'contacts_id' => $this->getParameter('id')
        ]);
    }

    public function renderCategories()
    {
        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        } else {
            $categoryId = null;
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table('contacts_categories')->where('parent_id', $categoryId)
            ->order('sorted DESC');
    }

    public function renderCategoriesDetail()
    {
        $this->template->menu = $this->database->table('contacts_categories')->get($this->getParameter('id'));
    }

}
