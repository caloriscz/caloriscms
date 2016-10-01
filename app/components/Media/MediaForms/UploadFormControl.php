<?php
namespace Caloriscz\Media\MediaForms;

use Nette\Application\UI\Control;

class UploadFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentUploadForm()
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

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'media/' . $form->values->pages_id;

            \App\Model\IO::directoryMake(APP_DIR . $ds . $storeFolder);

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
            if (\App\Model\IO::isImage($targetFile)) {
                \App\Model\IO::directoryMake(APP_DIR . $ds . $storeFolder . $ds . 'tn', 0755);

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

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/UploadFormControl.latte');

        $template->render();
    }

}