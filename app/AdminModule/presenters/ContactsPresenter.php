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


    protected function createComponentLoadVat()
    {
        $control = new \Caloriscz\Contacts\ContactForms\LoadVatControl($this->database);
        return $control;
    }

    /**
     * Insert communication
     */
    function createComponentInsertCommunicationForm()
    {
        $types = array(
            "E-mail" => "E-mail", "Telefon, domácí" => "Telefon, domácí",
            "Telefon, pracovní" => "Telefon, pracovní", "Fax" => "Fax",
            "Webová adresa" => "Webová adresa", "Skype" => "Skype"
        );

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addSelect('type', 'Typ komunikace', $types)
            ->setAttribute('class', 'form-control');
        $form->addText('type_value', 'Hodnota');
        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->setDefaults(array(
            "id" => $this->getParameter('id'),
        ));

        $form->onSuccess[] = $this->insertCommunicationFormSucceeded;
        return $form;
    }

    function insertCommunicationFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts_communication")
            ->insert(array(
                "contacts_id" => $form->values->id,
                "communication_type" => $form->values->type,
                "communication_value" => $form->values->type_value,
            ));

        $this->redirect(":Admin:Contacts:detailCommunications", array("id" => $form->values->id));
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
     * Delete contact with all other tables and related page
     */
    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $contacts = $this->database->table("contacts")->get($id[$a]);

            if ($contacts) {
                $page = $this->database->table("pages")->get($contacts->pages_id);

                if ($page) {
                    $doc = new Model\Document($this->database);
                    $doc->delete($page->id);
                    Model\IO::removeDirectory(APP_DIR . '/media/' . $page->id);
                }
            }
        }

        $this->redirect(":Admin:Contacts:default", array("id" => NULL));
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

    protected function createComponentContactsGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->id == NULL) {
            $contacts = $this->database->table("contacts");
        } else {
            $contacts = $this->database->table("contacts")->where("categories_id", $this->id);
        }

        $grid->setDataSource($contacts);
        $grid->addGroupAction($this->translator->translate('dictionary.main.Delete'))->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'Název')
            ->setRenderer(function ($item) {
                if (strlen($item->name) == 0 && strlen($item->company) == 0) {
                    $name = 'nemá název';
                } elseif (strlen($item->name) == 0) {
                    $name = $item->company;
                } else {
                    $name = $item->name;
                }

                $url = Nette\Utils\Html::el('a')->href($this->link('detail', array("id" => $item->pages_id)))
                    ->setText($name);
                return $url;
            })
            ->setSortable();
        $grid->addFilterText('name', $this->translator->translate('dictionary.main.Name'));
        $grid->addColumnText('email', $this->translator->translate('dictionary.main.Email'))
            ->setSortable();
        $grid->addFilterText('email', $this->translator->translate('dictionary.main.Email'));
        $grid->addColumnText('phone', $this->translator->translate('dictionary.main.Phone'))
            ->setSortable();
        $grid->addFilterText('phone', $this->translator->translate('dictionary.main.Phone'));
        $grid->addColumnText('vatin', $this->translator->translate('dictionary.main.VatIn'))
            ->setSortable();
        $grid->addFilterText('vatin', 'dictionary.main.VatIn');
        $grid->addColumnText('street', $this->translator->translate('dictionary.main.Address'))
            ->setRenderer(function ($item) {
                $address = $item->street . ', ' . $item->zip . ' ' . $item->city;
                if (strlen($address) > 2) {
                    $addressText = $address;
                } else {
                    $addressText = NULL;
                }
                return $addressText;
            })
            ->setSortable();
        $grid->addFilterText('street', 'dictionary.main.Street');

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
