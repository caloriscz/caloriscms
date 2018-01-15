<?php

namespace Caloriscz\Contact;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class ContactGridControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    /**
     * Ubladoo datagrid
     * @param $name
     */
    protected function createComponentContactsGrid($name)
    {

        $grid = new DataGrid($this, $name);

        if ($this->getPresenter()->id === null) {
            $contacts = $this->database->table('contacts');
        } else {
            $contacts = $this->database->table('contacts')->where('contacts_categories_id', $this->presenter->id);
        }

        try {
            $grid->setDataSource($contacts);
            $grid->addGroupAction($this->presenter->translator->translate('dictionary.main.Delete'))->onSelect[] = [$this, 'handleDelete'];

            $grid->addColumnLink('name', 'dictionary.main.Title')
                ->setRenderer(function ($item) {
                    if (strlen($item->name) === 0 && strlen($item->company) === 0) {
                        $name = 'nemá název';
                    } elseif (strlen($item->name) === 0) {
                        $name = $item->company;
                    } else {
                        $name = $item->name;
                    }

                    $url = Html::el('a')->href($this->getPresenter()->link('detail', array("id" => $item->id)))
                        ->setText($name);
                    return $url;
                })->setSortable();
            $grid->addFilterText('name', $this->getPresenter()->translator->translate('dictionary.main.Name'));
            $grid->addColumnText('email', $this->getPresenter()->translator->translate('dictionary.main.Email'))
                ->setSortable();
            $grid->addFilterText('email', $this->getPresenter()->translator->translate('dictionary.main.Email'));
            $grid->addColumnText('phone', $this->getPresenter()->translator->translate('dictionary.main.Phone'))
                ->setSortable();
            $grid->addFilterText('phone', $this->getPresenter()->translator->translate('dictionary.main.Phone'));
            $grid->addColumnText('vatin', $this->getPresenter()->translator->translate('dictionary.main.VatIn'))
                ->setSortable();
            $grid->addFilterText('vatin', 'dictionary.main.VatIn');
            $grid->addColumnText('street', $this->getPresenter()->translator->translate('dictionary.main.Address'))
                ->setRenderer(function ($item) {
                    $address = $item->street . ', ' . $item->zip . ' ' . $item->city;
                    if (strlen($address) > 2) {
                        $addressText = $address;
                    } else {
                        $addressText = null;
                    }
                    return $addressText;
                })
                ->setSortable();
            $grid->addFilterText('street', 'dictionary.main.Street');
        } catch (\Exception $e) {
            echo 'Grid error';
        }

        $grid->setTranslator($this->presenter->translator);
    }

    /**
     * Delete contact with all other tables and related page
     * @param $id
     */
    public function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $this->database->table('contacts')->get($id[$a])->delete();
        }

        $this->presenter->redirect(this, array('id' => null));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ContactGridControl.latte');

        $template->render();
    }

}