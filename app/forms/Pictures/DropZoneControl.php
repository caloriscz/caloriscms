<?php

namespace App\Forms\Pictures;

use App\Model\IO;
use App\Model\Picture;
use App\Model\Thumbnail;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class DropZoneControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Dropzone upload
     * @return BootstrapUIForm
     */
    protected function createComponentDropUploadForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal dropzone';
        $form->getElementPrototype()->role = 'form';

        $form->addHidden('pages_id');
        $form->addUpload('file_upload')
            ->setHtmlId('file_upload');
        $form->setDefaults(['pages_id' => $this->getPresenter()->getParameter('id'),
        ]);

        $form->onSuccess[] = [$this, 'dropUploadFormSucceeded'];
        return $form;
    }

    public function dropUploadFormSucceeded(BootstrapUIForm $form): void
    {
        (new \App\Model\MediaStorage($this->database, APP_DIR))->upload(
            'pictures', $form->values->pages_id, $_FILES['file'] ?? [],
            1, $this->getPresenter()->template->settings
        );
        $this->getPresenter()->sendJson(['saved' => true]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->setFile(__DIR__ . '/DropZoneControl.latte');
        $template->render();
    }
}
