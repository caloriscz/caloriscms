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

    protected function createComponentUploadForm()
    {
        $control = new \Caloriscz\Media\MediaForms\UploadFormControl($this->database);
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
        $doc = new Model\Document($this->database);
        $doc->delete($id);
        Model\IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->redirect(":Admin:Media:default", array("id" => null, "type" => $this->getParameter("type")));
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        if ($this->getParameter('mediatype') == 'image') {
            $this->context->httpResponse->setCookie('mediatype', 'image', '180 days');
        } else {
            $this->context->httpResponse->setCookie('mediatype', 'list', '180 days');
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
        $idMedia = $this->template->settings['categories:id:media'];

        if ($this->template->pageId == 6 && $this->getParameter('id') == false) {
            $pageId = 4;
        } elseif ($this->template->pageId == 8 && $this->getParameter('id') == false) {
            $pageId = 5;
        } else {
            $pageId = $this->getParameter('id');
        }

        $arr = array(
            "pages_types_id" => $this->template->pageId,
            "pages_id" => $pageId,
        );

        $this->template->media = $this->database->table("pages")->where($arr);

        if ($this->getParameter("id")) {
            $idN = $this->getParameter('id');
        } else {
            $idN = $idMedia;
        }

        $this->template->idN = $idN;

        $mediaDb = $this->database->table("media")
            ->where(array(
                'media.pages_id' => $idN,
                'pages.pages_types_id' => $this->template->pageId,
            ))
            ->order("name");

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

        $this->template->mediatype = $this->context->httpRequest->getCookie('mediatype');
    }

    function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
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