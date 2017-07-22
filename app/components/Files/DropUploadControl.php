<?php
namespace Caloriscz\Files;

use Nette\Application\UI\Control;

class DropUploadControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentDropUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->addUpload("file_upload")
            ->setHtmlId('file_upload');

        $form->onSuccess[] = [$this, "dropUploadFormSucceeded"];
        return $form;
    }

    function dropUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'images';

            $tempFile = $_FILES['file']['tmp_name'];
            $realFile = $_FILES['file']['name'];
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $_FILES['file']['name'];

            move_uploaded_file($tempFile, $targetFile);
            chmod($targetFile, 0644);

            exit();
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/DropUploadControl.latte');

        $template->render();
    }

}