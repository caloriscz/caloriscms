<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class FilesPresenter extends BasePresenter
{

    /**
     * Dropzone upload
     */
    function createComponentDropUploadForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->getElementPrototype()->class = "form-horizontal dropzone";
        $form->addUpload("file_upload")
            ->setHtmlId('file_upload');

        $form->onSuccess[] = $this->dropUploadFormSucceeded;
        return $form;
    }

    function dropUploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'images';

            $tempFile = $_FILES['file']['tmp_name'];
            $realFile = $_FILES['file']['name'];
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $_FILES['file']['name'];

            move_uploaded_file($tempFile, $targetFile);
            chmod($targetFile, 0644);

            exit();
        }
    }

    /**
     * Upload file form
     */
    function createComponentUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id', filter_input(INPUT_GET, "id"));
        $form->addUpload('the_file', 'File upload');
        $form->addSubmit('submitm', 'Nahrát');

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    /**
     * Upload file
     */
    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $filePath = _CALSET_PATHS_BASE . '/caloris_www/binaries/app/' . $filename;
        $filePathThumb = _CALSET_PATHS_BASE . '/caloris_www/binaries/app/t200_' . $filename;

        if (file_exists($filePath)) {
            $msg = 'Soubor již existuje';
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
        Model\IO::remove(APP_DIR . '/images/' . $_POST["filename"]);

        $this->redirect(":Admin:Files:default");
    }

    public
    function renderDefault()
    {
        //$dir = ;
        $this->template->files = \Nette\Utils\Finder::findFiles('')->in(APP_DIR . "/images");
    }

}
