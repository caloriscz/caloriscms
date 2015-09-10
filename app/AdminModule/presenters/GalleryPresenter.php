<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class GalleryPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Image Upload
     */
    function createComponentUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden('category');
        $form->addUpload('the_file', 'Vložit obrázek:')
                ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Neplatný typ obrázku:', $imageTypes);
        $form->addSelect('album', "Přesunout do:", $this->database->table("gallery_albums")->fetchPairs('id', 'title'))->setAttribute('class', 'form-control');
        $form->addTextarea("description", "Popisek:")
                ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "album" => $this->getParameter('id'),
            "category" => $this->getParameter('category'),
        ));
        $form->addSubmit('send', 'Vložit');

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $thumbName = 'tn_';

        $album = \Nette\Utils\Strings::padLeft($form->values->album, 4, '0');

        $fileDirectory = APP_DIR . '/images/gallery/' . $album . '/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $imageExists = $this->database->table('gallery')->where(array(
                'name' => $_FILES["the_file"]["name"],
                'gallery_albums_id' => $form->values->album,
            ));

            if ($imageExists->count() == 0) {
                $id = $this->database->table('gallery')->insert(array(
                    'name' => $_FILES["the_file"]["name"],
                    'gallery_categories_id' => $form->values->category,
                    'gallery_albums_id' => $form->values->album,
                    'description' => $form->values->description,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
				
                //Sort
                $this->database->table('gallery')->where(array("id" => $id))->update(array("sorted" => $id));
            }

            $fileName = $fileDirectory . $_FILES["the_file"]["name"];
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);

            // thumbnails
            $image = \Nette\Utils\Image::fromFile($fileName);
            $image->resize(400, 250, \Nette\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/images/gallery/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"]);
            chmod(APP_DIR . '/images/gallery/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"], 0777);
        }

        $this->redirect(":Admin:Gallery:detail", array(
            "id" => $form->values->album,
            "category" => $form->values->category,
        ));
    }

    /**
     * Dropzone upload
     */
    function createComponentDropUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->getElementPrototype()->id = "search-form";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("category");
        $form->addHidden("album");
        $form->addUpload("file_upload")
                ->setHtmlId('file_upload');
        $form->setDefaults(array(
            "album" => $this->getParameter('id'),
            "category" => $this->getParameter('category'),
        ));

        $form->onSuccess[] = $this->dropUploadFormSucceeded;
        return $form;
    }

    function dropUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $thumbName = 'tn_';
            $album = \Nette\Utils\Strings::padLeft($form->values->album, 4, '0');
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'images/gallery/' . $album . '/';

            $tempFile = $_FILES['file']['tmp_name'];          //3             
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;  //4

            $targetFile = $targetPath . $_FILES['file']['name'];  //5

            move_uploaded_file($tempFile, $targetFile); //6
            chmod($targetFile, 0644);
            $fileName = $_FILES["file"]["name"];
            // thumbnails
            $image = \Nette\Utils\Image::fromFile($targetFile);
            $image->resize(400, 250, \Nette\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save($targetPath . $thumbName . $_FILES["file"]["name"]);
            chmod($targetPath . $thumbName . $fileName, 0644);

            $checkImage = $this->database->table('gallery')->where(array(
                'name' => $fileName,
                'gallery_albums_id' => $form->values->album,
            ));

            if ($checkImage->count() == 0) {
                $id = $this->database->table('gallery')->insert(array(
                    'name' => $fileName,
                    'gallery_categories_id' => $form->values->category,
                    'gallery_albums_id' => $form->values->album,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
	
                //Sort
                $this->database->table('gallery')->where(array("id" => $id))->update(array("sorted" => $id));
            }
        }


        //$this->redirect(":Front:Homepage:default", $form->getValues(TRUE));
    }

    public function handleUpload($folder)
    {
        $fileUpload = new \Nette\Http\FileUpload($_FILES['uploadfile']);
        $this->upload->singleFileToDir($fileUpload, $folder);
    }

    /**
     * Delete image
     */
    function handleDelete($id)
    {
        $imageDb = $this->database->table("gallery")->get($id);

        \App\Model\IO::remove(APP_DIR . '/images/gallery/' . \Nette\Utils\Strings::padLeft($imageDb->gallery_albums_id, 4, '0') . '/' . $imageDb->name);
        \App\Model\IO::remove(APP_DIR . '/images/gallery/' . \Nette\Utils\Strings::padLeft($imageDb->gallery_albums_id, 4, '0') . '/tn_' . $imageDb->name);

        $imageDb->delete();

        $this->redirect(":Admin:Gallery:detail", array(
            "id" => $imageDb->gallery_albums_id,
            "category" => $imageDb->gallery_categories_id,
        ));
    }

    /**
     * Delete image
     */
    function handleDeleteImages($id)
    {
        $imageDb = $this->database->table("gallery")->where(array("gallery_albums_id" => $id));

        \App\Model\IO::removeDirectory(APP_DIR . '/images/gallery/' . \Nette\Utils\Strings::padLeft($id, 4, '0'));
        \App\Model\IO::directoryMake(APP_DIR . '/images/gallery/' . \Nette\Utils\Strings::padLeft($id, 4, '0'), 0755);

        $imageDb->delete();

        $this->redirect(":Admin:Gallery:detail", array(
            "id" => $id,
            "category" => $this->getParameter("category"),
        ));
    }

    /**
     * Move image up
     */
    function handleUp($id, $sorted, $album, $category)
    {
        $sortDb = $this->database->table("gallery")->where(array(
                    "sorted > ?" => $sorted,
                    "gallery_albums_id" => $album,
                ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("gallery")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("gallery")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Gallery:detail", array(
            "id" => $this->getParameter("album"),
            "category" => $this->getParameter("category"),
        ));
    }

    /**
     * Move image down
     */
    function handleDown($id, $sorted, $album)
    {
        $sortDb = $this->database->table("gallery")->where(array(
                    "sorted < ?" => $sorted,
                    "gallery_albums_id" => $album,
                ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("gallery")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("gallery")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Gallery:detail", array(
            "id" => $this->getParameter("album"),
            "category" => $this->getParameter("category"),
        ));
    }

    /**
     * Insert category
     */
    function createComponentInsertCategoryForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', "Název kategorie")
                ->setRequired('Vložte název alba');
        $form->addSubmit('send', 'Vytvořit nové kategorii');

        $form->onSuccess[] = $this->insertCategoryFormSucceeded;
        return $form;
    }

    function insertCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table('gallery_categories')
                ->insert(array(
            'title' => $form->values->title,
        ));

        $album = \Nette\Utils\Strings::padLeft($id, 4, '0');

        Model\IO::directoryMake(APP_DIR . '/images/gallery/' . $album, 0755);

        $this->redirect(":Admin:Gallery:default", array(
            "id" => $id,
        ));
    }

    /**
     * Album image Upload + info
     */
    function createComponentUploadCategoryForm()
    {
        $album = $this->database->table("gallery_categories")->get($this->getParameter('id'));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden('id');
        $form->addUpload('the_file', 'Přidat nebo změnit obrázek:')
                ->addCondition(Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Neplatný typ obrázku:', $imageTypes);
        $form->addText('title', "Název kategorie");
        $form->addTextarea("description", "Popisek:")
                ->setAttribute("class", "form-control")
                ->setHtmlId('wysiwyg-sm');
        $form->setDefaults(array(
            "id" => $this->getParameter('id'),
            "title" => $album->title,
            "description" => $album->description,
        ));
        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = $this->uploadCategoryFormSucceeded;
        return $form;
    }

    function uploadCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $album = \Nette\Utils\Strings::padLeft($form->values->id, 4, '0');

        $fileDirectory = APP_DIR . '/images/gallery-categories/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $fileName = $fileDirectory . $album . '.jpg';
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);
        }

        $this->database->table('gallery_categories')
                ->where(array("id" => $form->values->id))
                ->update(array(
                    'title' => $form->values->title,
                    'description' => $form->values->description,
        ));

        $this->redirect(":Admin:Gallery:category", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Delete category image
     */
    function handleDeleteCategoryImage($id)
    {
        \App\Model\IO::remove(APP_DIR . '/images/gallery-categories/' . Nette\Utils\Strings::padLeft($id, 4, '0') . '.jpg');

        $this->redirect(":Admin:Gallery:category", array("id" => $id));
    }

    /**
     * Delete category
     */
    function handleDeleteCategory($id)
    {
        $albums = $this->database->table('gallery_albums')->where(array("gallery_categories_id" => $id));

        foreach ($albums as $album) {
            foreach ($album->related('gallery', 'gallery_albums_id') as $gallery) {
                Model\IO::removeDirectory(APP_DIR . '/images/gallery/' . Nette\Utils\Strings::padLeft($album->id, 4, '0'));
            }

            Model\IO::remove(APP_DIR . '/images/gallery-albums/' . Nette\Utils\Strings::padLeft($album->id, 4, '0') . '.jpg');
        }

        Model\IO::remove(APP_DIR . '/images/gallery-categories/' . Nette\Utils\Strings::padLeft($id, 4, '0') . '.jpg');

        $this->database->table('gallery_categories')->get($id)->delete();

        $this->redirect(":Admin:Gallery:default");
    }

    /**
     * Album image Upload + info
     */
    function createComponentInsertAlbumForm()
    {
        $album = $this->database->table("gallery_albums")->get($this->getParameter('id'));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('category');
        $form->addText('title', "Název alba")
                ->setRequired('Vložte název alba');
        $form->setDefaults(array(
            "category" => $this->getParameter("id"),
         //   "title" => $album->title,
        ));
        $form->addSubmit('send', 'Vytvořit nové album');

        $form->onSuccess[] = $this->insertAlbumFormSucceeded;
        return $form;
    }

    function insertAlbumFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table('gallery_albums')
                ->insert(array(
            'gallery_categories_id' => $form->values->category,
            'title' => $form->values->title,
        ));

        $album = \Nette\Utils\Strings::padLeft($id, 4, '0');

        Model\IO::directoryMake(APP_DIR . '/images/gallery/' . $album, 0755);

        $this->redirect(":Admin:Gallery:album", array(
            "id" => $id,
        ));
    }

    /**
     * Album image Upload + info
     */
    function createComponentUploadAlbumForm()
    {
        $album = $this->database->table("gallery_albums")->get($this->getParameter('id'));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden('id');
        $form->addUpload('the_file', 'Přidat nebo změnit obrázek:')
                ->addCondition(Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Neplatný typ obrázku:', $imageTypes);
        $form->addText('title', "Název alba");
        $form->addTextarea("description", "Popisek:")
                ->setAttribute("class", "form-control")
                ->setHtmlId('wysiwyg');
        $form->setDefaults(array(
            "id" => $this->getParameter('id'),
            "title" => $album->title,
            "description" => $album->description,
        ));
        $form->addSubmit('send', 'Vložit');

        $form->onSuccess[] = $this->uploadAlbumFormSucceeded;
        return $form;
    }

    function uploadAlbumFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $album = \Nette\Utils\Strings::padLeft($form->values->id, 4, '0');

        $fileDirectory = APP_DIR . '/images/gallery-albums/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $fileName = $fileDirectory . $album . '.jpg';
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);

            // thumbnails
            //$image = \Nette\Utils\Image::fromFile($fileName);
            //$image->resize(400, 250, \Nette\Image::SHRINK_ONLY);
            //$image->sharpen();
            //$image->save(APP_DIR . '/images/gallery-albums/' . $album . ["name"]);
            //chmod(APP_DIR . '/images/gallery/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"], 0777);
        }

        $this->database->table('gallery_albums')
                ->where(array("id" => $form->values->id))
                ->update(array(
                    'title' => $form->values->title,
                    'description' => $form->values->description,
        ));

        $this->redirect(":Admin:Gallery:album", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Insert category
     */
    function createComponentEditImageForm()
    {
        $image = $this->database->table("gallery")->get($this->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addTextarea('description', "Popisek obrázku")
                ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "id" => $image->id,
            "description" => $image->description,
        ));

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = $this->editImageFormSucceeded;
        return $form;
    }

    function editImageFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table('gallery')
                ->get($form->values->id)->update(array(
            'description' => $form->values->description,
        ));


        $this->redirect(":Admin:Gallery:image", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Delete album
     */
    function handleDeleteAlbum($id)
    {
        $this->database->table('gallery_albums')->get($id)->delete();

        \App\Model\IO::removeDirectory(APP_DIR . '/images/gallery/' . Nette\Utils\Strings::padLeft($id, 4, '0'));
        \App\Model\IO::remove(APP_DIR . '/images/gallery-albums/' . Nette\Utils\Strings::padLeft($id, 4, '0') . '.jpg');

        $this->redirect(":Admin:Gallery:default");
    }

    /**
     * Delete album image
     */
    function handleDeleteAlbumImage($id)
    {
        \App\Model\IO::remove(APP_DIR . '/images/gallery-albums/' . Nette\Utils\Strings::padLeft($id, 4, '0') . '.jpg');

        $this->redirect(":Admin:Gallery:album", array("id" => $id));
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->getParameter('id');
        $this->template->category = $this->database->table("gallery_categories")
                ->get($this->getParameter("id"));
        $this->template->gallery = $this->database->table("gallery_albums")
                ->where(array("gallery_categories_id" => $this->getParameter('id')))
                ->order("title");
    }

    public function renderDetail()
    {
        $this->template->database = $this->database;
        $this->template->categoryId = $this->getParameter('category');
        $this->template->album = $this->database->table("gallery_albums")
                ->get($this->getParameter("id"));
        $this->template->gallery = $this->database->table("gallery")
                ->where(array("gallery_albums_id" => $this->getParameter('id')))
                ->order("sorted DESC");
    }

    public function renderAlbum()
    {
        $this->template->album = $this->database->table("gallery_albums")
                ->get($this->getParameter("id"));
    }

    public function renderCategory()
    {
        $this->template->category = $this->database->table("gallery_categories")
                ->get($this->getParameter("id"));
    }

}
