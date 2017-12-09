<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Image folder file manager
 */
class FilesPresenter extends BasePresenter
{

    protected function createComponentDropUploadFiles()
    {
        $control = new \Caloriscz\Files\DropUploadControl($this->database);
        return $control;
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
        $this->template->files = \Nette\Utils\Finder::findFiles('')->in(APP_DIR . '/images');
    }

}
