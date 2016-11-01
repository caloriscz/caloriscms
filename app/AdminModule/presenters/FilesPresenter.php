<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class FilesPresenter extends BasePresenter
{

    protected function createComponentDropUploadFiles()
    {
        $control = new \Caloriscz\Files\DropUploadControl($this->database);
        return $control;
    }

    /**
     * Delete files
     */
    function handleDelete()
    {
        Model\IO::remove(APP_DIR . '/images/' . $this->getParameter("path"));

        $this->redirect(this);
    }

    public function renderDefault()
    {
        $this->template->files = \Nette\Utils\Finder::findFiles('')->in(APP_DIR . "/images");
    }

}
