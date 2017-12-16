<?php

namespace App\AdminModule\Presenters;

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

        $this->template->page = $this->database->table("pages")->get($this->getParameter('id'));
    }

    /** @var \Caloriscz\Contacts\ContactForms\IEditContactControlFactory @inject */
    public $editContactControlFactory;

    protected function createComponentEditContact()
    {
        $control = $this->editContactControlFactory->create();
        $control->onSave[] = function ($pages_id, $error = null) {
            if ($error == 1) {
                $this->flashMessage($this->translator->translate('messages.sign.fillInEmail'), 'error');
            }

            $this->redirect(this, array("id" => $pages_id));
        };

        return $control;
    }

    protected function createComponentInsertContact()
    {
        $control = new \Caloriscz\Contacts\ContactForms\InsertContactControl($this->database);
        return $control;
    }

    protected function createComponentInsertHour()
    {
        $control = new \Caloriscz\Contacts\ContactForms\InsertHourControl($this->database);
        return $control;
    }

    protected function createComponentInsertCommunication()
    {
        $control = new \Caloriscz\Contacts\ContactForms\InsertCommunicationControl($this->database);
        return $control;
    }


    protected function createComponentLoadVat()
    {
        $control = new \Caloriscz\Contacts\ContactForms\LoadVatControl($this->database);
        return $control;
    }

    protected function createComponentContactGrid()
    {
        $control = new \Caloriscz\Contact\ContactGridControl($this->database);
        return $control;
    }

    protected function createComponentCommunicationGrid()
    {
        $control = new \Caloriscz\Contact\CommunicationGridControl($this->database);
        return $control;
    }

    protected function createComponentCategoryEdit()
    {
        $control = new \Caloriscz\Categories\EditCategoryControl($this->database);
        return $control;
    }

    protected function createComponentCategoryInsert()
    {
        $control = new \Caloriscz\Categories\InsertCategoryControl($this->database);
        return $control;
    }

    protected function createComponentContactCategories()
    {
        $control = new ContactCategoriesControl($this->database);
        return $control;
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

        $this->database->table('categories')->where('id', $category->getSubIds($id))
            ->delete();

        $this->redirect(':Admin:Categories:default');
    }

    public function handleUpCategory($id, $sorted)
    {
        $sortDb = $this->database->table('categories')->where(array(
            'sorted > ?' => $sorted,
            'parent_id' => $this->getParameter('category'),
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('categories')->where(array('id' => $id))->update(array('sorted' => $sort->sorted));
            $this->database->table('categories')->where(array('id' => $sort->id))
                ->update(array('sorted' => $sorted));
        }

        $this->redirect(":Admin:Categories:default", array("id" => null));
    }

    public function handleDownCategory($id, $sorted, $category)
    {
        $sortDb = $this->database->table('contacts_categories')->where(array(
            'sorted < ?' => $sorted,
            'parent_id' => $category,
        ))->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('contacts_categories')->where(array('id' => $id))->update(array('sorted' => $sort->sorted));
            $this->database->table('contacts_categories')->where(array('id' => $sort->id))->update(array('sorted' => $sorted));
        }

        $this->presenter->redirect(this, array("id" => null));
    }

    public function renderDefault()
    {
        $contactsDb = $this->database->table('contacts')->order('name');

        $paginator = new \Nette\Utils\Paginator;
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
        $this->template->communications = $this->database->table('contacts_communications')->where(array(
            'contacts_id' => $this->getParameter('id'),
        ));
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
