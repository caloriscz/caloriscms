<?php

namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

class InsertCarouselControl extends Control
{

    /** @var Explorer */
    public $database;

    public function __construct(Explorer $database)
    {
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
        $form->addUpload('the_file', 'Ikonka')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
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

        try {
            (new \App\Model\AssetWrites($this->database, APP_DIR))->carousel(null, [
                'title' => $form->values->title, 'description' => $form->values->description,
                'uri' => $form->values->uri, 'visible' => $form->values->visible,
            ], $_FILES['the_file'] ?? []);
        } catch (\Nette\Application\BadRequestException $error) {
            $this->presenter->flashMessage($error->getMessage(), 'error');
        }
        $this->redirect('this', ['carousel_id' => $form->values->carousel_id]);

    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/InsertCarouselControl.latte');
        $this->template->render();
    }

}
