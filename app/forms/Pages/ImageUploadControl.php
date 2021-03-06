<?php
namespace App\Forms\Pages;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Utils\Image;

class ImageUploadControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * File Upload
     */
    protected function createComponentUploadFilesForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addUpload('the_file', 'Vložit odstrávek');
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('send', 'Vložit');

        $form->setDefaults([
            'id' => $this->presenter->getParameter('id'),
        ]);

        $form->onSuccess[] = [$this, 'uploadFilesFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function uploadFilesFormSucceeded(BootstrapUIForm $form): void
    {
        $album = $form->values->id;
        $fileDirectory = APP_DIR . '/media/' . $album . '/';
        IO::directoryMake($fileDirectory, 0755);

        if (strlen($_FILES['the_file']['tmp_name']) > 1) {
            $imageExists = $this->database->table('media')->where([
                'name' => $_FILES['the_file']['name'],
                'pages_id' => $form->values->id,
            ]);

            if ($imageExists->count() === 0) {
                $this->database->table('media')->insert([
                    'name' => $_FILES['the_file']['name'],
                    'pages_id' => $form->values->id,
                    'description' => $form->values->description,
                    'date_created' => date('Y-m-d H:i:s'),
                    'file_type' => 0,
                ]);
            }

            $fileName = $fileDirectory . $_FILES['the_file']['name'];
            IO::remove($fileName);

            copy($_FILES['the_file']['tmp_name'], $fileName);
            chmod($fileName, 0644);
        }

        $this->redirect('this', [
            'id' => $form->values->id,
        ]);
    }

    /**
     * Image Upload
     * @return BootstrapUIForm
     */
    protected function createComponentUploadForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $imageTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

        $form->addHidden('id');
        $form->addUpload('the_file', 'Vložit obrázek')
            ->addRule(Form::MIME_TYPE, 'Neplatný typ', $imageTypes);
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('send', 'Obrázek');

        $form->setDefaults([
            'id' => $this->presenter->getParameter('id'),
        ]);

        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\UnknownImageFileException
     */
    public function uploadFormSucceeded(BootstrapUIForm $form): void
    {
        $fileDirectory = APP_DIR . '/media/' . $form->values->id;
        IO::directoryMake($fileDirectory, 0755);

        if (strlen($_FILES['the_file']['tmp_name']) > 1) {
            $imageExists = $this->database->table('media')->where([
                'name' => $_FILES['the_file']['name'],
                'pages_id' => $form->values->id,
            ]);

            $fileName = $fileDirectory . '/' . $_FILES['the_file']['name'];
            IO::remove($fileName);

            copy($_FILES['the_file']['tmp_name'], $fileName);
            chmod($fileName, 0644);

            if ($imageExists->count() === 0) {
                $this->database->table('media')->insert([
                    'name' => $_FILES['the_file']['name'],
                    'pages_id' => $form->values->id,
                    'description' => $form->values->description,
                    'filesize' => filesize($fileDirectory . '/' . $_FILES['the_file']['name']),
                    'file_type' => 1,
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            }

            // thumbnails
            $image = Image::fromFile($fileName);
            $image->resize(400, 250, Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/media/' . $form->values->id . '/tn/' . $_FILES['the_file']['name']);
            chmod(APP_DIR . '/media/' . $form->values->id . '/tn/' . $_FILES['the_file']['name'], 0644);
        }

        $this->redirect('this', [
            'id' => $form->values->id,
            'category' => $form->values->category,
        ]);
    }

    public function render($id = 0): void
    {
        $this->template->id = $id;
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/ImageUploadControl.latte');
        $template->render();
    }

}
