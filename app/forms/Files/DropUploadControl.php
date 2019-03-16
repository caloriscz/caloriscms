<?php
namespace App\Forms\Files;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class DropUploadControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentDropUploadForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->getElementPrototype()->class = 'form-horizontal dropzone';
        $form->addUpload('file_upload')
            ->setHtmlId('file_upload');

        $form->onSuccess[] = [$this, 'dropUploadFormSucceeded'];
        return $form;
    }

    public function dropUploadFormSucceeded(): void
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'images';

            $tempFile = $_FILES['file']['tmp_name'];
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $_FILES['file']['name'];

            move_uploaded_file($tempFile, $targetFile);
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