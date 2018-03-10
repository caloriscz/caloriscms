<?php

namespace Caloriscz\Page;

use App\Forms\Menu\InsertMenuControl;
use App\Model\Document;
use App\Model\Entity\Pages;
use App\Model\IO;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Database\Context;

class PageListControl extends Control
{

    /** @var Context @inject */
    public $database;

    public $onSave;
    public $view;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentMenuInsert()
    {
        return new InsertMenuControl($this->database);
    }

    /**
     * Delete page
     * @param $id
     */
    public function handleDelete($id)
    {
        $doc = new Document($this->database);
        $doc->delete($id);
        IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->onSave($this->getParameter('type'));
    }

    /**
     * Change to public or private - private pages are not show to visitor
     */
    public function handlePublic()
    {
        $show = 1;
        $page = $this->database->table('pages')->get($this->getParameter('id'));

        if ($page->public === 1) {
            $show = 0;
        }

        $this->database->table('pages')->get($this->getParameter('id'))->update(['public' => $show]);

        $this->onSave($this->getParameter('type'));
    }

    public function setView($view = false): ?string
    {
        $this->view = $view;
        return $this->view;
    }

    public function getView(): ?string
    {
        if (empty($this->view)) {
            return $this->view;
        }

        return $this->view;
    }

    public function render($type = 1, $view = 'simple')
    {
        $template = $this->getTemplate();
        $template->category = null;

        if ($this->getView() === 'tree') {
            $template->setFile(__DIR__ . '/PageListTreeControl.latte');
        } elseif ($this->getView() === 'gallery') {
            $template->setFile(__DIR__ . '/PageListGalleryControl.latte');
        } else {
            $template->setFile(__DIR__ . '/PageListSimpleControl.latte');
        }

        $order = 'FIELD(id, 1, 3, 4, 6, 2), title';

        if ($type === 2) {
            $order = 'title';
        }

        if ($this->getView() === 'tree') {
            $template->pages = $this->database->table('pages')->where('pages_id', null)->order($order);
        } else {
            $template->pages = $this->database->table('pages')->where('pages_types_id', $type)->order('title ASC');
        }

        $template->settings = $this->getPresenter()->template->settings;
        $template->type = $type;
        $template->database = $this->database;

        $template->render();
    }

}
