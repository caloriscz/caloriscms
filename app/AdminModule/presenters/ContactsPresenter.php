<?php

namespace App\AdminModule\Presenters;

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

        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->contact = $this->database->table("contacts")
            ->where(array("pages_id" => $this->template->page->id))->fetch();
        $this->template->user = $this->database->table("users")->get($this->template->contact->users_id);
    }

    protected function createComponentEditContact()
    {
        $control = new \Caloriscz\Contacts\ContactForms\EditContactControl($this->database);
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

    /**
     * Delete contact for communication
     */
    function handleDeleteCommunication($id)
    {
        $contacts = $this->database->table("contacts_communication")->get($id);
        $contactsId = $contacts->contacts_id;
        $contacts->delete();
        $this->redirect(":Admin:Contacts:detailCommunications", array("id" => $contactsId));
    }

    /**
     * Delete hour
     */
    function handleDeleteHour($id)
    {
        $this->database->table("contacts_openinghours")->get($id)->delete();

        $this->redirect(":Admin:Contacts:detailOpeningHours", array("id" => $this->getParameter("page")));
    }

    public function createComponentCommunicationsGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        $dbCommunications = $this->database->table("contacts_communication")
            ->where(array("contacts_id" => $this->getParameter('id')));

        $grid->setDataSource($dbCommunications);
        $grid->setItemsPerPageList(array(20));

        $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDeleteCommunication'];

        $grid->addColumnText('communication_type', 'Typ');
        $grid->addColumnText('communication_value', 'Hodnota');

        $grid->setTranslator($this->translator);
    }

    

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:contact'];
        $contactsDb = $this->database->table("contacts")->order("name");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($contactsDb->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->args = $this->getParameters();
        $this->template->paginator = $paginator;
        $this->template->contacts = $contactsDb->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->menu = $this->database->table("categories")->where('parent_id', $this->template->settings['categories:id:contact']);
    }

    public function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderImages()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderMember()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderCommunications()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->communications = $this->database->table("contacts_communications")->where(array(
            "contacts_id" => $this->getParameter('id'),
        ));
    }

}
