<?php

namespace Caloriscz\Media;

use App\Model\File;
use App\Model\Thumbnail;
use Nette\Application\UI\Control;
use Tracy\Debugger;

class DropZoneControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Dropzone upload
     */
    function createComponentDropUploadForm($id)
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->addHidden("pages_id");
        $form->addUpload("file_upload")
            ->setHtmlId('file_upload');
        $form->setDefaults(array(
            "pages_id" => $this->presenter->getParameter('id'),
        ));

        $form->onSuccess[] = $this->dropUploadFormSucceeded;
        return $form;
    }

    function dropUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $storeFolder = 'media/' . $form->values->pages_id;
            $fileName = $_FILES['file']['name'];
            $targetFile = APP_DIR . "/" . $storeFolder . "/" . $fileName;

            \App\Model\IO::directoryMake(APP_DIR . "/" . $storeFolder, 0755);

            move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
            chmod($targetFile, 0644);

            $checkImage = $this->database->table("media")->where(array(
                'name' => $fileName,
                'pages_id' => $form->values->id,
            ));

            if ($checkImage->count() == 0) {
                $file = new File($this->database);
                $file->setPageId($form->values->pages_id);
                $file->setType(1);
                $file->setFile($fileName);
                $file->create();

                $thumb = new Thumbnail;
                $thumb->setFile("/media/" . $form->values->pages_id, $fileName);
                $thumb->setDimensions($this->presenter->template->settings["media_thumb_width"],
                    $this->presenter->template->settings["media_thumb_height"]);
                $thumb->save($this->presenter->template->settings["media_thumb_dir"]);
            }
        }

        exit();
    }

    /**
     * Dropzone file upload
     */
    function createComponentDropFileUploadForm($id)
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->addHidden("pages_id");
        $form->addUpload("file_upload")
            ->setHtmlId('file_upload');
        $form->setDefaults(array(
            "pages_id" => $this->presenter->getParameter('id'),
        ));

        $form->onSuccess[] = $this->dropFileUploadFormSucceeded;
        return $form;
    }

    function dropFileUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'media/' . $form->values->pages_id;

            \App\Model\IO::directoryMake(APP_DIR . $ds . $storeFolder, 0755);

            $tempFile = $_FILES['file']['tmp_name'];
            $realFile = $_FILES['file']['name'];
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $_FILES['file']['name'];

            move_uploaded_file($tempFile, $targetFile);
            chmod($targetFile, 0644);
            $fileSize = filesize($targetFile);

            $checkImage = $this->database->table("media")->where(array(
                'name' => $realFile,
                'pages_id' => $form->values->id,
            ));

            if ($checkImage->count() == 0) {
                $this->database->table("media")->insert(array(
                    'name' => $realFile,
                    'pages_id' => $form->values->pages_id,
                    'filesize' => $fileSize,
                    'file_type' => 0,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
            } else {
                echo "Nejsem reálný soubor";
            }
        }

        exit();
    }

    public function render($id)
    {
        $this->template->id = $id;
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/DropZoneControl.latte');

        $template->render();
    }

}
