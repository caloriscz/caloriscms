<?php

namespace Caloriscz\Page;

use App\Forms\Menu\InsertMenuControl;
use App\Model\Document;
use App\Model\IO;
use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class PageListControl extends Control
{

    public Explorer $database;

    public $onSave;
    public $view;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentMenuInsert(): InsertMenuControl
    {
        return new InsertMenuControl($this->database);
    }

    /**
     * Delete page
     * @param $id
     */
    public function handleDelete($id): void
    {
        $doc = new Document($this->database);
        $doc->delete($id);
        IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->onSave($this->getParameter('type'));
    }

    /**
     * Change to public or private - private pages are not show to visitor
     */
    public function handlePublic(): void
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

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }


    public function render(?int $type = null): void
    {
        $template = $this->getTemplate();
        $template->category = null;
        $arr = [];

        if ($this->getView() === 'tree') {
            $template->setFile(__DIR__ . '/PageListTreeControl.latte');
        } elseif ($this->getView() === 'gallery') {
            $template->setFile(__DIR__ . '/PageListGalleryControl.latte');
        } else {
            $template->setFile(__DIR__ . '/PageListSimpleControl.latte');
        }

        $order = 'FIELD(id, 1, 3, 4, 6, 2), title';

        if ($this->presenter->getParameter('type') != null) {
            $type = $this->presenter->getParameter('type');
        }

        if ($type === 2) {
            $order = 'title';
        }

        if (is_numeric($type)) {
            $arr['pages_types_id'] = $type;
        }


        $arr['NOT pages_types_id'] = null;


        if ($this->getView() === 'tree') {
            $arr['pages_id'] = null;

            $template->pages = $this->database->table('pages')->where($arr)->order($order);
        } else {
            $pages = $this->database->table('pages')->where($arr)->order('title ASC');

            $paginator = new Paginator();
            $paginator->setItemCount($pages->count('*'));
            $paginator->setItemsPerPage(20);
            $paginator->setPage($this->presenter->getParameter('page') ?? 1);

            $template->pages = $pages->limit($paginator->getLength(), $paginator->getOffset());
            $template->paginator = $paginator;
            $template->args = $this->getPresenter()->getParameters();
        }

        $template->settings = $this->getPresenter()->template->settings;
        $template->type = $type;
        $template->database = $this->database;

        $template->render();
    }

}
