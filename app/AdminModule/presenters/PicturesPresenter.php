<?php

namespace App\AdminModule\Presenters;

use App\Forms\Media\EditFileControl;
use App\Forms\Media\EditPictureFormControl;
use App\Forms\Pictures\InsertPictureControl;
use App\Forms\Pictures\DropZoneControl;
use App\Model\Category;
use App\Model\Document;
use App\Model\IO;
use App\Model\Page;
use Caloriscz\Pictures\PageThumbControl;
use Nette\Http\FileUpload;
use Nette\Utils\Paginator;


/**
 * Picture gallery presenter
 */
class PicturesPresenter extends BasePresenter
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
        return new EditPictureFormControl($this->database);
    }

    protected function createComponentPageThumb()
    {
        return new PageThumbControl($this->database);
    }

    public function handleUpload($folder)
    {
        $fileUpload = new FileUpload($_FILES['uploadfile']);
        $this->upload->singleFileToDir($fileUpload, $folder);
    }

    protected function createComponentDropZone()
    {
        return new DropZoneControl($this->database);
    }

    /**
     * Delete image
     * @param $id
     * @param $type
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id, $type)
    {
        $imageDb = $this->database->table('pictures')->get($id);

        IO::remove(APP_DIR . '/pictures/' . $imageDb->pages_id . '/' . $imageDb->name);
        IO::remove(APP_DIR . '/pictures/' . $imageDb->pages_id . '/tn/' . $imageDb->name);

        $imageDb->delete();

        $this->redirect(this, array(
            'id' => $imageDb->pages_id,
            'type' => $this->getParameter('type'),
        ));
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
            $doc = new Document($this->database);
            $doc->delete($item);
            IO::removeDirectory(APP_DIR . '/pictures/' . $item);
        }

        $this->redirect('this', array('id' => null, 'type' => $this->getParameter('type')));
    }

    /**
     * Toggle display
     */
    public function handleToggle()
    {
        if ($this->getParameter('mediatype') === 'image') {
            $this->response->setCookie('mediatype', 'image', '180 days');
        } else {
            $this->response->setCookie('mediatype', 'list', '180 days');
        }


        $this->redirect(':Admin:Media:default', array('type' => $this->getParameter('type')));
    }

    /**
     * Move image up
     * @param $id
     * @param $sorted
     * @param $album
     * @throws \Nette\Application\AbortException
     */
    public function handleUp($id, $sorted, $album)
    {
        $sortDb = $this->database->table('media')->where(array(
            'sorted > ?' => $sorted,
            'categories_id' => $album,
        ))->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('media')->where(array('id' => $id))->update(array('sorted' => $sort->sorted));
            $this->database->table('media')->where(array('id' => $sort->id))->update(array('sorted' => $sorted));
        }

        $this->redirect(':Admin:Media:detail', array(
            'id' => $this->getParameter('album'),
            'category' => $this->getParameter('category'),
        ));
    }

    /**
     * Move image down
     * @param $id
     * @param $sorted
     * @param $album
     * @throws \Nette\Application\AbortException
     */
    public function handleDown($id, $sorted, $album)
    {
        $sortDb = $this->database->table('media')->where([
            'sorted < ?' => $sorted,
            'categories_id' => $album,
        ])->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('media')->where(array('id' => $id))->update(array('sorted' => $sort->sorted));
            $this->database->table('media')->where(array('id' => $sort->id))->update(array('sorted' => $sorted));
        }

        $this->redirect(':Admin:Media:detail', array(
            'id' => $this->getParameter('album'),
            'category' => $this->getParameter('category'),
        ));
    }

    public function renderDefault()
    {
        $mediaDb = $this->database->table('pictures')->where(['pages_id' => $this->getParameter('id')]);

        $paginator = new Paginator();
        $paginator->setItemCount($mediaDb->count('*'));
        $paginator->setItemsPerPage(16);
        $paginator->setPage($this->getParameter('page'));

        $this->template->args = $this->getParameters();

        $this->template->documents = $mediaDb->order('name');
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

    public function renderDetail()
    {
        $this->template->file = $this->database->table('pictures')->get($this->getParameter('id'));

        $isItImage = mime_content_type(APP_DIR . '/pictures/' . $this->template->file->pages_id . '/' . $this->template->file->name);

        if (null !== $isItImage) {
            $type = [
                'image/png', 'image/jpg', 'image/jpeg', 'image/jpe', 'image/gif',
                'image/tif', 'image/tiff', 'image/svg', 'image/ico', 'image/icon', 'image/x-icon'];

            if (in_array($isItImage, $type, true)) {
                $this->template->isImage = true;
            } else {
                $this->template->isImage = false;
            }

            $this->template->fileType = $isItImage;
        }
    }
}