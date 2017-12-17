<?php
namespace Caloriscz\Contact;

use Nette\Application\UI\Control;

class CommunicationGridControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function createComponentCommunicationGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        $dbCommunications = $this->database->table('contacts_communication')
            ->where(array('contacts_id' => $this->presenter->getParameter('id')));

        $grid->setDataSource($dbCommunications);
        $grid->setItemsPerPageList(array(20));

        $grid->addGroupAction('dictionary.main.Delete')->onSelect[] = [$this, 'handleDeleteCommunication'];

        $grid->addColumnText('communication_type', 'Typ');
        $grid->addColumnText('communication_value', 'Hodnota');

        $grid->setTranslator($this->presenter->translator);
    }

    public function handleDeleteCommunication($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $contacts = $this->database->table('contacts_communication')->get($id);
            $contactsId = $contacts->contacts_id;
            $contacts->delete();
            $this->presenter->redirect(this, array('id' => $contactsId));
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CommunicationGridControl.latte');

        $template->render();
    }

}