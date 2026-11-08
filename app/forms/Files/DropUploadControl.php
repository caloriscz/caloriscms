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
        $file = $_FILES['file'] ?? [];
        try {
            $name = \App\Model\AssetStorage::imageName($file);
            (new \App\Model\AssetStorage($this->database, APP_DIR))->run(static function ($assets) use ($file, $name): void {
                $assets->image($file, 'images/' . $name);
            });
        } catch (\Nette\Application\BadRequestException $error) {
            // Dropzone shows a JSON "error" string next to the file.
            $this->getPresenter()->getHttpResponse()->setCode($error->getHttpCode());
            $this->getPresenter()->sendJson(['error' => $error->getMessage()]);
        }
        $this->getPresenter()->sendJson(['saved' => true]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/DropUploadControl.latte');

        $template->render();
    }
}
