<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Files\DropUploadControl;
use App\Model;
use Nette\Utils\Finder;

/**
 * Image folder file manager
 */
class FilesPresenter extends BasePresenter
{

    protected function createComponentDropUploadFiles()
    {
        return new DropUploadControl($this->database);
    }

    /**
     * Delete file
     */
    public function handleDelete()
    {
        Model\IO::remove(APP_DIR . '/images/' . $this->getParameter('path'));

        $this->redirect(this);
    }

    public function renderDefault()
    {
        $this->template->files = Finder::findFiles('')->in(APP_DIR . '/images');
    }

}
