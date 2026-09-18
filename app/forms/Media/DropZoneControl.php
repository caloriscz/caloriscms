<?php

namespace App\Forms\Media;

use App\Model\IO;
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
     * Dropzone file upload
     * @param $id
     * @return BootstrapUIForm
     */
    protected function createComponentDropForm($id): BootstrapUIForm
    {
        $type = 0;

        if ($this->getPresenter()->getView() === 'albums') {
            $type = 1;
        }

        $form = new BootstrapUIForm();

        $form->getElementPrototype()->class = 'form-horizontal dropzone';
        $form->addHidden('pages_id');
        $form->addHidden('type');
        $form->addUpload('file_upload')
            ->setHtmlId('file_upload');
        $form->setDefaults([
            'pages_id' => $this->getPresenter()->getParameter('id'),
            'type' => $type,
        ]);

        $form->onSuccess[] = [$this, 'dropFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function dropFormSucceeded(BootstrapUIForm $form): void
    {
        (new \App\Model\MediaStorage($this->database, APP_DIR))->upload(
            'media', $form->values->pages_id, $_FILES['file'] ?? [],
            (int) $form->values->type, $this->getPresenter()->template->settings
        );
        $this->getPresenter()->sendJson(['saved' => true]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->setFile(__DIR__ . '/DropZoneControl.latte');
        $template->render();
    }
}
