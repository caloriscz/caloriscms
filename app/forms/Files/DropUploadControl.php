<?php
namespace App\Forms\Files;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class DropUploadControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentDropUploadForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal dropzone';
        $form->addUpload('file_upload')->setHtmlId('file_upload');

        $form->onSuccess[] = [$this, 'dropUploadFormSucceeded'];
        return $form;
    }

    public function dropUploadFormSucceeded(): void
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'images';

            $tempFile = $_FILES['file']['tmp_name'];
            $fileName = IO::sanitizeUploadFileName($_FILES['file']['name']);

            if ($fileName === null || !IO::isAllowedImageUpload($_FILES['file']['name'], $tempFile)) {
                http_response_code(400);
                exit();
            }

            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $fileName;

            if (!move_uploaded_file($tempFile, $targetFile)) {
                http_response_code(400);
                exit();
            }

            chmod($targetFile, 0644);

            exit();
        }
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/DropUploadControl.latte');

        $template->render();
    }
}
