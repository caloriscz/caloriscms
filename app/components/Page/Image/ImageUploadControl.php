<?php
namespace Caloriscz\Page\Image;

use App\Model\IO;
use Nette\Application\UI\Control;

class ImageUploadControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * File Upload
     */
    public function createComponentUploadFilesForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addUpload('the_file', 'dictionary.main.InsertFile');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults(array(
            'id' => $this->presenter->getParameter('id'),
        ));

        $form->onSuccess[] = [$this, 'uploadFilesFormSucceeded'];
        return $form;
    }

    public function uploadFilesFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $album = $form->values->id;
        $fileDirectory = APP_DIR . '/media/' . $album . '/';
        \App\Model\IO::directoryMake($fileDirectory, 0755);

        if (strlen($_FILES['the_file']['tmp_name']) > 1) {
            $imageExists = $this->database->table('media')->where(array(
                'name' => $_FILES['the_file']['name'],
                'pages_id' => $form->values->id,
            ));

            if ($imageExists->count() == 0) {
                $this->database->table('media')->insert(array(
                    'name' => $_FILES['the_file']['name'],
                    'pages_id' => $form->values->id,
                    'description' => $form->values->description,
                    'date_created' => date('Y-m-d H:i:s'),
                    'file_type' => 0,
                ));
            }

            $fileName = $fileDirectory . $_FILES['the_file']['name'];
            IO::remove($fileName);

            copy($_FILES['the_file']['tmp_name'], $fileName);
            chmod($fileName, 0644);
        }

        $this->redirect(this, array(
            'id' => $form->values->id,
        ));
    }

    /**
     * Image Upload
     */
    public function createComponentUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden('id');
        $form->addUpload('the_file', 'dictionary.main.InsertImage')
            ->addRule(\Nette\Forms\Form::MIME_TYPE, 'messages.error.invalidTypeOfMessage', $imageTypes);
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'dictionary.main.Image');

        $form->setDefaults(array(
            'id' => $this->presenter->getParameter('id'),
        ));

        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];
        return $form;
    }

    public function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $fileDirectory = APP_DIR . '/media/' . $form->values->id;
        \App\Model\IO::directoryMake($fileDirectory, 0755);

        if (strlen($_FILES['the_file']['tmp_name']) > 1) {
            $imageExists = $this->database->table('media')->where(array(
                'name' => $_FILES['the_file']['name'],
                'pages_id' => $form->values->id,
            ));

            $fileName = $fileDirectory . '/' . $_FILES['the_file']['name'];
            \App\Model\IO::remove($fileName);

            copy($_FILES['the_file']['tmp_name'], $fileName);
            chmod($fileName, 0644);

            if ($imageExists->count() == 0) {
                $this->database->table('media')->insert(array(
                    'name' => $_FILES['the_file']['name'],
                    'pages_id' => $form->values->id,
                    'description' => $form->values->description,
                    'filesize' => filesize($fileDirectory . '/' . $_FILES['the_file']['name']),
                    'file_type' => 1,
                    'date_created' => date('Y-m-d H:i:s'),
                ));
            }

            // thumbnails
            $image = \Nette\Utils\Image::fromFile($fileName);
            $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/media/' . $form->values->id . '/tn/' . $_FILES['the_file']['name']);
            chmod(APP_DIR . '/media/' . $form->values->id . '/tn/' . $_FILES['the_file']['name'], 0644);
        }

        $this->redirect(this, array(
            'id' => $form->values->id,
            'category' => $form->values->category,
        ));
    }

    public function render($id = 0)
    {
        $this->template->id = $id;
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/ImageUploadControl.latte');

        $template->render();
    }

}
