<?php

namespace App\AdminModule\Presenters;

use App\Forms\Media\EditPictureFormControl;


/**
 * Picture gallery presenter
 */
class PicturesPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->pageId = $this->getParameter('type');
    }

    protected function createComponentEditFile()
    {
        return new EditPictureFormControl($this->database);
    }

    public function renderDetail()
    {
        $this->template->file = $this->database->table('pictures')->get($this->getParameter('id'));

        $isItImage = mime_content_type(APP_DIR . '/pictures/' . $this->template->file->pages_id . '/' . $this->template->file->name);

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
}