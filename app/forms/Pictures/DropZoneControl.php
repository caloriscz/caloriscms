<?php

namespace App\Forms\Pictures;

use App\Model\IO;
use App\Model\Picture;
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
     * @return BootstrapUIForm
     */
    protected function createComponentDropUploadForm()
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
            $storeFolder = 'pictures/' . $form->values->pages_id;
            $fileName = $_FILES['file']['name'];
            $targetFile = APP_DIR . '/' . $storeFolder . '/' . $fileName;

            IO::directoryMake(APP_DIR . '/' . $storeFolder);
            IO::directoryMake(APP_DIR . '/' . $storeFolder . '/tn');

            move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
            chmod($targetFile, 0644);

            $checkImage = $this->database->table('pictures')->where([
                'name' => $fileName,
                'pages_id' => $form->values->pages_id,
            ]);

            if ($checkImage->count() === 0) {
                $file = new Picture($this->database);
                $file->setPageId($form->values->pages_id);
                $file->setType(1);
                $file->setFile($fileName);
                $file->create();

                $thumb = new Thumbnail;
                $thumb->setFile('/pictures/' . $form->values->pages_id, $fileName);
                $thumb->setDimensions($this->getPresenter()->template->settings['media_thumb_width'],
                    $this->getPresenter()->template->settings['media_thumb_height']);
                $thumb->save($this->getPresenter()->template->settings['media_thumb_dir']);
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
