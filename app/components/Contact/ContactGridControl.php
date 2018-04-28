<?php

namespace Caloriscz\Contact;

use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class ContactGridControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    /**
     * Ubladoo datagrid
     * @param $name
     * @throws \Nette\InvalidStateException
     */
    protected function createComponentContactsGrid($name): void
    {
        $grid = new DataGrid();
        $this->addComponent($grid, $name);


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
                    if ('' === $item->name && '' === $item->company) {
                        $name = 'nemá název';
                    } elseif ('' === $item->name) {
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
                    if (\strlen($address) > 2) {
                        $addressText = $address;
                    } else {
                        $addressText = null;
                    }
                    return $addressText;
                })
                ->setSortable();
            $grid->addFilterText('street', 'dictionary.main.Street');
        } catch (Exception $e) {
            echo 'Grid error';
        }

        $grid->setTranslator($this->presenter->translator);
    }

    /**
     * Delete contact with all other tables and related page
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id): void
    {
        for ($a = 0, $aMax = \count($id); $a < $aMax; $a++) {
            $this->database->table('contacts')->get($id[$a])->delete();
        }

        $this->presenter->redirect('this', ['id' => null]);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ContactGridControl.latte');

        $template->render();
    }

}