<?php

namespace Caloriscz\Media;

use App\Model\Category;
use App\Model\IO;
use App\Security\CsrfProtectedMutation;
use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class ImageBrowserControl extends Control
{
    use CsrfProtectedMutation;

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;

    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    /**
     * Delete image
     * @param $id
     * @param $type
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id): void
    {
        $this->requireCsrfToken();

        $pageId = (new \App\Model\MediaStorage($this->database, APP_DIR))->deleteFile(
            'pictures', (int) $id, (string) ($this->presenter->template->settings['media_thumb_dir'] ?? 'tn')
        ) ?? $this->getParameter('id');

        $this->redirect('this', [
            'id' => $pageId,
            'type' => $this->getParameter('type'),
        ]);
    }

    /**
     * Set image as main  image
     */
    public function handleSetMain(): void
    {
        $this->requireCsrfToken();

        // Set all other media images in this folder as 0
        $this->database->table('pictures')->where(['pages_id' => $this->getParameter('id')])
            ->update(['main_file' => 0]);

        // Set chosen one as the main one
        $this->database->table('pictures')->get($this->getParameter('image'))->update(['main_file' => 1]);


        $this->presenter->redirect('this', ['id' => $this->getParameter('id'), 'image' => $this->getParameter('image')]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $mediaDb = $this->database->table('pictures')->where(['pages_id' => $this->presenter->getParameter('id')]);

        $paginator = new Paginator();
        $paginator->setItemCount($mediaDb->count('*'));
        $paginator->setItemsPerPage(16);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $template->args = $this->presenter->getParameters();
        $template->documents = $mediaDb->order('name');
        $template->paginator = $paginator;
        $template->productsArr = $mediaDb->limit($paginator->getLength(), $paginator->getOffset());
        $template->csrfToken = $this->getCsrfToken();

        if ($this->getParameter('id')) {
            $category = new Category($this->database);
            $template->breadcrumbs = $category->getPageBreadcrumb($this->presenter->getParameter('id'));
        } else {
            $template->breadcrumbs = [];
        }


        $template->setFile(__DIR__ . '/ImageBrowserControl.latte');
        $template->render();
    }
}
