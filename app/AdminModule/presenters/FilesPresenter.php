<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class FilesPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Upload file form
     */
    function createComponentUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id', filter_input(INPUT_GET, "id"));
        $form->addUpload('the_file', 'admin.files.choosefile');
        $form->addSubmit('submitm', 'admin.files.upload');

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    /**
     * Upload file
     */
    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $filePath = _CALSET_PATHS_BASE . '/www/binaries/app/' . $filename;
        $filePathThumb = _CALSET_PATHS_BASE . '/www/binaries/app/t200_' . $filename;

        if (file_exists($filePath)) {
            $msg = 'File exists';
        }

        copy($_FILES["the_file"]["tmp_name"], $filePath);
        chmod($filePath, 0755);

        return $params;
    }

    /**
     * Delete files
     */
    function deleteFormSucceeded()
    {
        \Caloris\IO::remove(_CALSET_PATHS_BASE . '/www/images/' . $_POST["filename"]);
        \Caloris\IO::remove(_CALSET_PATHS_BASE . '/www/images/t200_' . $_POST["filename"]);

        $this->redirect("Files:default");
    }

    public function renderDefault()
    {
        //$dir = ;
        $this->template->files = \Nette\Utils\Finder::findFiles('')->in(APP_DIR . "/files");
    }

}
