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

    function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('category');
        $form->addHidden('type');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea("preview", "dictionary.main.Description")
            ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "category" => $this->getParameter("id"),
            "type" => $this->getParameter("type")
        ));

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new Model\Document($this->database);
        $doc->setType($form->values->type);
        $doc->setTitle($form->values->title);
        $doc->setPreview($form->values->preview);
        $page = $doc->create($this->user->getId(), $form->values->category);

        Model\IO::directoryMake(APP_DIR . '/media/' . $page);

        $this->redirect(":Admin:Media:default", array(
            "id" => $form->values->category,
            "type" => $form->values->type,
        ));
    }


    /**
     * Image Upload
     */
    function createComponentUploadForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('category');
        $form->addUpload('the_file', 'Vložit obrázek:');
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "album" => $this->getParameter('id'),
        ));
        $form->addSubmit('send', 'dictionary.main.Send');

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $album = $form->values->album;

        $fileDirectory = APP_DIR . '/media/' . $album . '/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $imageExists = $this->database->table("media")->where(array(
                'name' => $_FILES["the_file"]["name"],
                'categories_id' => $form->values->album,
            ));

            if ($imageExists->count() == 0) {
                $fileName = $fileDirectory . $_FILES["the_file"]["name"];
                \App\Model\IO::remove($fileName);

                copy($_FILES["the_file"]["tmp_name"], $fileName);
                chmod($fileName, 0644);

                $this->database->table("media")->insert(array(
                    'name' => $_FILES["the_file"]["name"],
                    'description' => $form->values->description,
                    'date_created' => date("Y-m-d H:i:s"),
                    'filesize' => filesize($fileName),
                ));
            }
        }

        $this->redirect(":Admin:Media:detail", array(
            "id" => $form->values->album,
        ));
    }

    /**
     * Dropzone upload
     */
    function createComponentDropUploadForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->addHidden("pages_id");
        $form->addUpload("file_upload")
            ->setHtmlId('file_upload');
        $form->setDefaults(array(
            "pages_id" => $this->getParameter('id'),
        ));

        $form->onSuccess[] = $this->dropUploadFormSucceeded;
        return $form;
    }

    function dropUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'media/' . $form->values->pages_id;

            Model\IO::directoryMake(APP_DIR . $ds . $storeFolder, 0755);

            $tempFile = $_FILES['file']['tmp_name'];          //3             
            $realFile = $_FILES['file']['name'];          //3             
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;  //4

            $targetFile = $targetPath . $_FILES['file']['name'];  //5

            move_uploaded_file($tempFile, $targetFile); //6
            chmod($targetFile, 0644);
            $fileSize = filesize($targetFile);
            //$fileType = pathinfo($realFile, PATHINFO_EXTENSION);
            //$fileTypeC = str_replace(array("doc", "docx", "xlsx", "xls"), array("word", "word", "excel", "excel"), $fileType);

            $checkImage = $this->database->table("media")->where(array(
                'name' => $realFile,
                'pages_id' => $form->values->id,
            ));

            // Thumbnail for images
            if (Model\IO::isImage($targetFile)) {
                Model\IO::directoryMake(APP_DIR . $ds . $storeFolder . $ds . 'tn', 0755);

                // thumbnails
                $image = \Nette\Utils\Image::fromFile($targetFile);
                $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
                $image->sharpen();
                $image->save(APP_DIR . '/media/' . $form->values->pages_id . '/tn/' . $realFile);
                chmod(APP_DIR . '/media/' . $form->values->pages_id . '/tn/' . $realFile, 0644);
            }

            if ($checkImage->count() == 0) {
                $this->database->table("media")->insert(array(
                    'name' => $realFile,
                    'pages_id' => $form->values->pages_id,
                    'filesize' => $fileSize,
                    'file_type' => 1,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
            } else {
                echo "Nejsem reálný soubor";
            }
        }

        exit();
    }

    public function handleUpload($folder)
    {
        $fileUpload = new \Nette\Http\FileUpload($_FILES['uploadfile']);
        $this->upload->singleFileToDir($fileUpload, $folder);
    }

    function createComponentUploadImageJs()
    {
        $form = $this->baseFormFactory->createUI();
        $form->onSuccess[] = $this->uploadImageJsFormSucceeded;
        return $form;
    }

    function uploadImageJsFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {

        // todo peekay Finish ImageJsForm
        $folder = '';

        //$fileUpload = new \Nette\Http\FileUpload($_FILES['uploadfile']);
        //$this->upload->singleFileToDir($fileUpload, $folder);

        echo '{
    "uploaded": 1,
    "fileName": "foto.png",
    "url": "http://demo.cz/foto.png"
}';
        exit();
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

    /**
     * Edit file information
     */
    function createComponentEditFileForm()
    {
        $image = $this->database->table("media")->get($this->getParameter("id"));

        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea('description', "dictionary.main.Description")
            ->setAttribute("style", "height: 200px;")
            ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "id" => $image->id,
            "title" => $image->title,
            "description" => $image->description,
        ));

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = $this->editFileFormSucceeded;
        return $form;
    }

    function editFileFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("media")
            ->get($form->values->id)->update(array(
                'title' => $form->values->title,
                'description' => $form->values->description,
            ));


        $this->redirect(":Admin:Media:image", array(
            "id" => $form->values->id,
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

    function renderDetail() {
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
