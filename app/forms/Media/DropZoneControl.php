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

        $form->onSuccess[] = [$this, 'dropFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function dropFormSucceeded(BootstrapUIForm $form): void
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

            $fileSize = filesize($targetFile);

            $checkImage = $this->database->table('media')->where([
                'name' => $realFile,
                'pages_id' => $form->values->pages_id,
            ]);

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

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->setFile(__DIR__ . '/DropZoneControl.latte');
        $template->render();
    }
}
