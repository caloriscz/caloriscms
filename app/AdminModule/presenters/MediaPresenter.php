<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class MediaPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->pageId = $this->getParameter("type");
    }

    protected function createComponentInsertMediaForm()
    {
        $control = new \Caloriscz\Media\MediaForms\InsertMediaControl($this->database);
        return $control;
    }

    protected function createComponentEditFile()
    {
        $control = new \Caloriscz\Media\MediaForms\EditFileControl($this->database);
        return $control;
    }

    protected function createComponentPageThumb()
    {
        $control = new \Caloriscz\Page\Pages\PageThumbControl($this->database);
        return $control;
    }

    public function handleUpload($folder)
    {
        $fileUpload = new \Nette\Http\FileUpload($_FILES['uploadfile']);
        $this->upload->singleFileToDir($fileUpload, $folder);
    }

    /**
     * Delete image
     */
    function handleDelete($id, $type)
    {
        $imageDb = $this->database->table("media")->get($id);

        \App\Model\IO::remove(APP_DIR . '/media/' . $imageDb->pages_id . '/' . $imageDb->name);
        \App\Model\IO::remove(APP_DIR . '/media/' . $imageDb->pages_id . '/tn/' . $imageDb->name);

        $imageDb->delete();

        $this->redirect(":Admin:Media:default", array(
            "id" => $imageDb->pages_id,
            "type" => $this->getParameter("type"),
        ));
    }

    /**
     * Delete page
     */
    function handleDeletePage($id)
    {
        $page = new Model\Page($this->database);
        $pages = $page->getChildren($id);
        $pages[] = $id;

        foreach ($pages as $item) {
            echo 1;
            $doc = new Model\Document($this->database);
            $doc->delete($item);
            Model\IO::removeDirectory(APP_DIR . '/media/' . $item);
        }

        $this->redirect(this, array("id" => null, "type" => $this->getParameter("type")));
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        if ($this->getParameter('mediatype') == 'image') {
            $this->response->setCookie('mediatype', 'image', '180 days');
        } else {
            $this->response->setCookie('mediatype', 'list', '180 days');
        }


        $this->redirect(":Admin:Media:default", array("type" => $this->getParameter("type")));
    }

    /**
     * Move image up
     */
    function handleUp($id, $sorted, $album)
    {
        $sortDb = $this->database->table("media")->where(array(
            "sorted > ?" => $sorted,
            "categories_id" => $album,
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("media")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("media")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Media:detail", array(
            "id" => $this->getParameter("album"),
            "category" => $this->getParameter("category"),
        ));
    }

    /**
     * Move image down
     */
    function handleDown($id, $sorted, $album)
    {
        $sortDb = $this->database->table("media")->where(array(
            "sorted < ?" => $sorted,
            "categories_id" => $album,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("media")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("media")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Media:detail", array(
            "id" => $this->getParameter("album"),
            "category" => $this->getParameter("category"),
        ));
    }

    public function renderDefault()
    {
        if ($this->getParameter("id")) {
            $arr = array(
                'media.pages_id' => $this->getParameter('id'),
                'file_type' => 0,
            );

            $this->template->idN = $this->getParameter('id');
        } else {
            $arr = array(
                'file_type' => 0,
            );
        }

        $mediaDb = $this->database->table("media")->where($arr)->order("name");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($mediaDb->count("*"));
        $paginator->setItemsPerPage(10);
        $paginator->setPage($this->getParameter("page"));

        $this->template->args = $this->getParameters();

        $this->template->documents = $mediaDb;
        $this->template->mediaType = $this->getParameter('cat');

        $this->template->paginator = $paginator;
        $this->template->productsArr = $mediaDb->limit($paginator->getLength(), $paginator->getOffset());

        if ($this->getParameter("id")) {
            $category = new Model\Category($this->database);
            $this->template->breadcrumbs = $category->getPageBreadcrumb($this->getParameter("id"));
        }

        $this->template->mediatype = $this->request->getCookie('mediatype');
    }

    public function renderAlbums()
    {
        if ($this->getParameter("id")) {
            $arr = array(
                'media.pages_id' => $this->getParameter('id'),
                'file_type' => 1,
            );

            $this->template->idN = $this->getParameter('id');
        } else {
            $arr = array(
                'file_type' => 1,
            );
        }

        $mediaDb = $this->database->table("media")->where($arr)->order("name");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($mediaDb->count("*"));
        $paginator->setItemsPerPage(10);
        $paginator->setPage($this->getParameter("page"));

        $this->template->args = $this->getParameters();

        $this->template->documents = $mediaDb;
        $this->template->mediaType = $this->getParameter('cat');

        $this->template->paginator = $paginator;
        $this->template->productsArr = $mediaDb->limit($paginator->getLength(), $paginator->getOffset());

        if ($this->getParameter("id")) {
            $category = new Model\Category($this->database);
            $this->template->breadcrumbs = $category->getPageBreadcrumb($this->getParameter("id"));
        }

        $this->template->mediatype = $this->request->getCookie('mediatype');
    }

    public function renderImage()
    {
        $this->template->file = $this->database->table("media")
            ->get($this->getParameter('id'));

        $isItImage = mime_content_type(APP_DIR . '/media/' . $this->template->file->pages_id . '/' . $this->template->file->name);

        if (isset($isItImage)) {
            $type = array(
                'image/png', 'image/jpg', 'image/jpeg', 'image/jpe', 'image/gif',
                'image/tif', 'image/tiff', 'image/svg', 'image/ico', 'image/icon', 'image/x-icon');

            if (in_array($isItImage, $type)) {
                $this->template->isImage = true;
            } else {
                $this->template->isImage = false;
            }

            $this->template->fileType = $isItImage;
        }
    }

}