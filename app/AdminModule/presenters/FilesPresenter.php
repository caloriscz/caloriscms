<?php

namespace App\AdminModule\Presenters;

use App\Forms\Files\DropUploadControl;
use App\Forms\Media\EditFileFormControl;
use App\Forms\Media\EditPictureFormControl;
use App\Model;
use Nette\Utils\Finder;

/**
 * Image folder file manager
 */
class FilesPresenter extends BasePresenter
{

    protected function createComponentDropUploadFiles(): DropUploadControl
    {
        return new DropUploadControl($this->database);
    }

    /**
     * Delete file
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete(): void
    {
        Model\IO::remove(APP_DIR . '/images/' . $this->getParameter('path'));

        $this->redirect('this');
    }

    public function renderDefault(): void
    {
        $this->template->files = Finder::findFiles('')->in(APP_DIR . '/images');
    }

    protected function createComponentEditFile(): EditFileFormControl
    {
        return new EditFileFormControl($this->database);
    }

    protected function createComponentEditPicture(): EditPictureFormControl
    {
        return new EditPictureFormControl($this->database);
    }

    public function renderDetailPicture(): void
    {
        $this->template->files = $this->database->table('pictures')->get($this->getParameter('id'));

        $isItImage = mime_content_type(APP_DIR . '/pictures/' . $this->template->files->pages_id . '/' . $this->template->files->name);

        if (null !== $isItImage) {
            $this->template->isImage = false;

            $type = [
                'image/png', 'image/jpg', 'image/jpeg', 'image/jpe', 'image/gif',
                'image/tif', 'image/tiff', 'image/svg', 'image/ico', 'image/icon', 'image/x-icon'];

            if (in_array($isItImage, $type, true)) {
                $this->template->isImage = true;
            }

            $this->template->fileType = $isItImage;
        }
    }

    public function renderDetailFile(): void
    {
        $this->template->files = $this->database->table('media')->get($this->getParameter('id'));
    }

}
