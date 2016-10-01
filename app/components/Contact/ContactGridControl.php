<?php
namespace Caloriscz\Contact;

use Nette\Application\UI\Control;

class ContactGridControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentContactsGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->presenter->id == null) {
            $contacts = $this->database->table("contacts");
        } else {
            $contacts = $this->database->table("contacts")->where("categories_id", $this->id);
        }

        $grid->setDataSource($contacts);
        $grid->addGroupAction($this->presenter->translator->translate('dictionary.main.Delete'))->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'Název')
            ->setRenderer(function ($item) {
                if (strlen($item->name) == 0 && strlen($item->company) == 0) {
                    $name = 'nemá název';
                } elseif (strlen($item->name) == 0) {
                    $name = $item->company;
                } else {
                    $name = $item->name;
                }

                $url = \Nette\Utils\Html::el('a')->href($this->presenter->link('detail', array("id" => $item->pages_id)))
                    ->setText($name);
                return $url;
            })
            ->setSortable();
        $grid->addFilterText('name', $this->presenter->translator->translate('dictionary.main.Name'));
        $grid->addColumnText('email', $this->presenter->translator->translate('dictionary.main.Email'))
            ->setSortable();
        $grid->addFilterText('email', $this->presenter->translator->translate('dictionary.main.Email'));
        $grid->addColumnText('phone', $this->presenter->translator->translate('dictionary.main.Phone'))
            ->setSortable();
        $grid->addFilterText('phone', $this->presenter->translator->translate('dictionary.main.Phone'));
        $grid->addColumnText('vatin', $this->presenter->translator->translate('dictionary.main.VatIn'))
            ->setSortable();
        $grid->addFilterText('vatin', 'dictionary.main.VatIn');
        $grid->addColumnText('street', $this->presenter->translator->translate('dictionary.main.Address'))
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

        $grid->setTranslator($this->presenter->translator);
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
                    $doc = new \App\Model\Document($this->database);
                    $doc->delete($page->id);
                    \App\Model\IO::removeDirectory(APP_DIR . '/media/' . $page->id);
                }
            }
        }

        $this->presenter->redirect(this, array("id" => null));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ContactGridControl.latte');

        $template->render();
    }

}