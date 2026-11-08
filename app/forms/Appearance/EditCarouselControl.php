<?php

namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

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
        $form->addUpload('the_file', 'Ikonka')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
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

        try {
            (new \App\Model\AssetWrites($this->database, APP_DIR))->carousel((int) $form->values->carousel_id, [
                'title' => $form->values->title, 'description' => $form->values->description,
                'uri' => $form->values->uri, 'visible' => $form->values->visible,
            ], $_FILES['the_file'] ?? []);
        } catch (\Nette\Application\BadRequestException $error) {
            $this->presenter->flashMessage($error->getMessage(), 'error');
        }
        $this->redirect('this', ['carousel_id' => $form->values->carousel_id]);

    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditCarouselControl.latte');
        $this->template->render();
    }

}
