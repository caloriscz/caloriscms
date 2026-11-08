<?php

namespace App\AdminModule\Presenters;

use App\Forms\Files\DropUploadControl;
use App\Forms\Media\EditFileFormControl;
use App\Forms\Media\EditPictureFormControl;
use App\Model;
use App\Security\CsrfProtectedMutation;
use Nette\Utils\Finder;

/**
 * Image folder file manager
 */
class FilesPresenter extends BasePresenter
{
    use CsrfProtectedMutation;

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
        $this->requireCsrfToken();

        $path = (string) $this->getParameter('path');
        // Not basename(): on PHP 7.4 in the C locale it drops a leading non-ASCII
        // letter ('Žluť.png' becomes 'luť.png'). A separator would reach the
        // managed folders ('menu/1.png').
        if (strpbrk($path, '/\\') !== false) { $this->error('Invalid asset path.', 400); }
        // remove() accepts any plain basename, including legacy names with spaces
        // or diacritics; dotfiles, '..' and control characters are a 400 there.
        (new Model\AssetStorage($this->database, APP_DIR))->run(static function ($assets) use ($path): void {
            $assets->remove('images/' . $path);
        });

        $this->redirect('this');
    }

    public function renderDefault(): void
    {
        $this->template->files = Finder::findFiles('')->in(APP_DIR . '/images');
        $this->template->csrfToken = $this->getCsrfToken();
    }

    protected function createComponentEditFile(): EditFileFormControl
    {
        return new EditFileFormControl($this->database);
    }

    protected function createComponentEditPicture(): EditPictureFormControl
    {
        return new EditPictureFormControl($this->database);
    }

    public function actionDetailPicture(): void
    {
        $this->template->files = $this->requireRecord('pictures');
    }

    public function renderDetailPicture(): void
    {

        // A row whose file is gone shows no preview instead of a mime_content_type() warning.
        $path = APP_DIR . '/pictures/' . $this->template->files->pages_id . '/' . $this->template->files->name;
        $isItImage = is_file($path) ? mime_content_type($path) : false;

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

    public function actionDetailFile(): void
    {
        $this->template->files = $this->requireRecord('media');
    }

}
