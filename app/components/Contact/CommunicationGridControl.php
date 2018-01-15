<?php
namespace Caloriscz\Contact;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Ublaboo\DataGrid\DataGrid;

class CommunicationGridControl extends Control
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function createComponentCommunicationGrid($name)
    {
        $grid = new DataGrid($this, $name);

        $dbCommunications = $this->database->table('contacts_communication')
            ->where(['contacts_id' => $this->presenter->getParameter('id')]);

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
            $this->getPresenter()->redirect(this, array('id' => $contactsId));
        }
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/CommunicationGridControl.latte');
        $this->template->render();
    }

}