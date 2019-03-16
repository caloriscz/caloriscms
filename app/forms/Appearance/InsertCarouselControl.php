<?php

namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertCarouselControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Edit category
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('carousel_id');
        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popisek')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'max-height: 150px;');
        $form->addText('uri', 'Odkaz');
        $form->addCheckbox('visible', 'Zobrazit');
        $form->addUpload('the_file', 'Ikonka');
        $form->addSubmit('submitm', 'Uložit');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $image = $form->values->the_file->name;

        $arr = [
            'title' => $form->values->title,
            'description' => $form->values->description,
            'uri' => $form->values->uri,
            'visible' => $form->values->visible,
            'image' => $form->values->the_file
        ];

        if ($form->values->the_file->error === 0) {
            $arr['image'] = $image;

            if (file_exists(APP_DIR . '/images/carousel/' . $image)) {
                IO::remove(APP_DIR . '/images/carousel/' . $image);
                IO::upload(APP_DIR . '/images/carousel/', $image);
            } else {
                IO::upload(APP_DIR . '/images/carousel/', $image);
            }
        }

        $this->database->table('carousel')->insert($arr);


        $this->redirect('this', ['carousel_id' => $form->values->carousel_id]);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/InsertCarouselControl.latte');
        $this->template->render();
    }

}
