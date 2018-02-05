<?php

namespace App\AdminModule\Presenters;

use App\Forms\Media\DropZoneControl;
use App\Forms\Media\EditFileControl;
use App\Forms\Media\InsertPictureControl;
use App\Model\Category;
use App\Model\Document;
use App\Model\IO;
use App\Model\Page;
use Caloriscz\Media\PageThumbControl;
use Nette\Http\FileUpload;
use Nette\Utils\Paginator;


/**
 * Homepage presenter.
 */
class MediaPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->pageId = $this->getParameter('type');
    }

    protected function createComponentInsertMediaForm()
    {
        return new InsertPictureControl($this->database);
    }

    protected function createComponentEditFile()
    {
        return new EditFileControl($this->database);
    }

    protected function createComponentPageThumb()
    {
        return new PageThumbControl($this->database);
    }

    protected function createComponentDropZone()
    {
        return new DropZoneControl($this->database);
    }

    public function handleUpload($folder)
    {
        $fileUpload = new FileUpload($_FILES['uploadfile']);
        $this->upload->singleFileToDir($fileUpload, $folder);
    }

    /**
     * Delete file
     * @param $id
     * @param $type
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $imageDb = $this->database->table('media')->get($id);

        IO::remove(APP_DIR . '/media/' . $imageDb->pages_id . '/' . $imageDb->name);

        $imageDb->delete();

        $this->redirect('this', [
            'id' => $imageDb->pages_id,
            'type' => $this->getParameter('type'),
        ]);
    }

    /**
     * Delete page
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDeletePage($id)
    {
        $page = new Page($this->database);
        $pages = $page->getChildren($id);
        $pages[] = $id;

        foreach ($pages as $item) {
            echo 1;
            $doc = new Document($this->database);
            $doc->delete($item);
            IO::removeDirectory(APP_DIR . '/media/' . $item);
        }

        $this->redirect('this', ['id' => null, 'type' => $this->getParameter('type')]);
    }

    /**
     * Toggle display
     */
    public function handleToggle()
    {
        if ($this->getParameter('mediatype') == 'image') {
            $this->response->setCookie('mediatype', 'image', '180 days');
        } else {
            $this->response->setCookie('mediatype', 'list', '180 days');
        }

        $this->redirect(':Admin:Media:default', ['type' => $this->getParameter('type')]);
    }

    /**
     * Move document up
     * @param $id
     * @param $sorted
     * @param $album
     * @throws \Nette\Application\AbortException
     */
    public function handleUp($id, $sorted, $album)
    {
        $sortDb = $this->database->table('media')->where([
            'sorted > ?' => $sorted,
            'categories_id' => $album,
        ])->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('media')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('media')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Media:detail', [
            'id' => $this->getParameter('album'),
            'category' => $this->getParameter('category'),
        ]);
    }

    /**
     * Move document down
     * @param $id
     * @param $sorted
     * @param $album
     * @throws \Nette\Application\AbortException
     */
    public function handleDown($id, $sorted, $album)
    {
        $sortDb = $this->database->table('media')->where(array(
            'sorted < ?' => $sorted,
            'categories_id' => $album,
        ))->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('media')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('media')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Media:detail', [
            'id' => $this->getParameter('album'),
            'category' => $this->getParameter('category'),
        ]);
    }

    public function renderDefault()
    {
        if ($this->getParameter('id')) {
            $arr = [
                'media.pages_id' => $this->getParameter('id'),
                'file_type' => 0,
            ];

            $this->template->idN = $this->getParameter('id');
        } else {
            $arr = [
                'file_type' => 3,
            ];
        }

        $mediaDb = $this->database->table('media')->where($arr)->order('name');

        $paginator = new Paginator();
        $paginator->setItemCount($mediaDb->count('*'));
        $paginator->setItemsPerPage(10);
        $paginator->setPage($this->getParameter('page'));

        $this->template->args = $this->getParameters();

        $this->template->documents = $mediaDb;
        $this->template->mediaType = $this->getParameter('cat');

        $this->template->paginator = $paginator;
        $this->template->productsArr = $mediaDb->limit($paginator->getLength(), $paginator->getOffset());

        if ($this->getParameter('id')) {
            $category = new Category($this->database);
            $this->template->breadcrumbs = $category->getPageBreadcrumb($this->getParameter('id'));
        } else {
            $this->template->breadcrumbs = [];
        }

        $this->template->mediatype = $this->request->getCookie('mediatype');
    }
}