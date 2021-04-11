<?php

namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditCarouselControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit category
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $carousel = $this->database->table('carousel')->get($this->getPresenter()->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->addHidden('carousel_id');
        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popisek')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'max-height: 150px;');
        $form->addText('uri', 'Odkaz');
        $form->addCheckbox('visible', 'Zobrazit');
        $form->addUpload('the_file', 'Ikonka');
        $form->addSubmit('submitm', 'Uložit');

        $arr = [
            'carousel_id' => $carousel->id,
            'title' => $carousel->title,
            'description' => $carousel->description,
            'visible' => $carousel->visible,
            'uri' => $carousel->uri,
        ];

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $arr = [
            'title' => $form->values->title,
            'description' => $form->values->description,
            'uri' => $form->values->uri,
            'visible' => $form->values->visible,
        ];

        if ($form->values->the_file->error === 0) {
            $image = $form->values->the_file->name;

            $arr['image'] = $image;

            if (file_exists(APP_DIR . '/images/carousel/' . $image)) {
                IO::remove(APP_DIR . '/images/carousel/' . $image);
                IO::upload(APP_DIR . '/images/carousel/', $image);
            } else {
                IO::upload(APP_DIR . '/images/carousel/', $image);
            }
        }

        $this->database->table('carousel')->get($form->values->carousel_id)->update($arr);

        $this->redirect('this', ['carousel_id' => $form->values->carousel_id]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditCarouselControl.latte');
        $this->template->render();
    }

}