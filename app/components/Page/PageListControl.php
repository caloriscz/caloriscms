<?php

namespace Caloriscz\Page;

use App\Model\Document;
use App\Model\IO;
use App\Security\CsrfProtectedMutation;
use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class PageListControl extends Control
{
    use CsrfProtectedMutation;

    /** List views the admin can choose. The old 'tree' view was removed (Taskino c260ce18). */
    public const VIEWS = ['simple', 'gallery'];

    public Explorer $database;

    public $onSave;
    public $view;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * The stored view, or 'simple' for anything else (such as a 'tree' cookie from before).
     * @param mixed $view
     */
    public static function normalizeView($view): string
    {
        return in_array($view, self::VIEWS, true) ? $view : 'simple';
    }

    /**
     * Delete page
     * @param $id
     */
    public function handleDelete($id): void
    {
        $this->requireCsrfToken();

        $doc = new Document($this->database);
        $doc->delete($id);

        $this->onSave($this->getParameter('type'));
    }

    /**
     * Change to public or private - private pages are not show to visitor
     */
    public function handlePublic(): void
    {
        $this->requireCsrfToken();

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
        $this->view = self::normalizeView($view);
        return $this->view;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }


    public function render(?int $type = null): void
    {
        $template = $this->getTemplate();
        $arr = [];

        if ($this->getView() === 'gallery') {
            $template->setFile(__DIR__ . '/PageListGalleryControl.latte');
        } else {
            $template->setFile(__DIR__ . '/PageListSimpleControl.latte');
        }

        if ($this->presenter->getParameter('type') != null) {
            $type = $this->presenter->getParameter('type');
        }

        if (is_numeric($type)) {
            $arr['pages_types_id'] = $type;
        }


        $arr['NOT pages_types_id'] = null;

        $pages = $this->database->table('pages')->where($arr)->order('title ASC');

        $paginator = new Paginator();
        $paginator->setItemCount($pages->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $template->pages = $pages->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->getPresenter()->getParameters();

        $template->settings = $this->getPresenter()->template->settings;
        $template->type = $type;
        $template->database = $this->database;
        $template->csrfToken = $this->getCsrfToken();

        $template->render();
    }

}
