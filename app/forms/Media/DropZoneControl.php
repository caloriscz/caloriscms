<?php

namespace App\Forms\Media;

use App\Model\File;
use App\Model\IO;
use App\Model\Thumbnail;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class DropZoneControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Dropzone upload
     * @param $id
     * @return BootstrapUIForm
     */
    protected function createComponentDropUploadForm($id)
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

    public function dropUploadFormSucceeded(BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $storeFolder = 'media/' . $form->values->pages_id;
            $fileName = $_FILES['file']['name'];
            $targetFile = APP_DIR . '/' . $storeFolder . '/' . $fileName;

            IO::directoryMake(APP_DIR . '/' . $storeFolder, 0755);

            move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
            chmod($targetFile, 0644);

            $checkImage = $this->database->table('media')->where([
                'name' => $fileName,
                'pages_id' => $form->values->pages_id,
            ]);

            if ($checkImage->count() === 0) {
                $file = new File($this->database);
                $file->setPageId($form->values->pages_id);
                $file->setType(1);
                $file->setFile($fileName);
                $file->create();

                $thumb = new Thumbnail;
                $thumb->setFile('/media/' . $form->values->pages_id, $fileName);
                $thumb->setDimensions($this->getPresenter()->template->settings['media_thumb_width'],
                    $this->getPresenter()->template->settings['media_thumb_height']);
                $thumb->save($this->getPresenter()->template->settings['media_thumb_dir']);
            }
        }

        exit();
    }

    /**
     * Dropzone file upload
     */
    public function createComponentDropFileUploadForm($id)
    {
        $type = 0;

        if ($this->getPresenter()->getView() === 'albums') {
            $type = 1;
        }

        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = 'form-horizontal dropzone';
        $form->addHidden('pages_id');
        $form->addHidden('type');
        $form->addUpload('file_upload')
            ->setHtmlId('file_upload');
        $form->setDefaults([
            'pages_id' => $this->getPresenter()->getParameter('id'),
            'type' => $type,
        ]);

        $form->onSuccess[] = [$this, 'dropFileUploadFormSucceeded'];

        return $form;
    }

    public function dropFileUploadFormSucceeded(BootstrapUIForm $form)
    {
        if (!empty($_FILES)) {
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'media/' . $form->values->pages_id;

            IO::directoryMake(APP_DIR . $ds . $storeFolder);

            $tempFile = $_FILES['file']['tmp_name'];
            $realFile = $_FILES['file']['name'];
            $targetPath = APP_DIR . $ds . $storeFolder . $ds;

            $targetFile = $targetPath . $_FILES['file']['name'];

            move_uploaded_file($tempFile, $targetFile);
            chmod($targetFile, 0644);
            $fileSize = filesize($targetFile);

            $checkImage = $this->database->table('media')->where(array(
                'name' => $realFile,
                'pages_id' => $form->values->id,
            ));

            if ($checkImage->count() === 0) {
                $this->database->table('media')->insert([
                    'name' => $realFile,
                    'pages_id' => $form->values->pages_id,
                    'filesize' => $fileSize,
                    'file_type' =>  $form->values->type,
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            } else {
                echo 'Nejsem reálný soubor';
            }
        }

        exit();
    }

    public function render($id)
    {
        $this->template->id = $id;
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->setFile(__DIR__ . '/DropZoneControl.latte');
        $template->render();
    }
}
