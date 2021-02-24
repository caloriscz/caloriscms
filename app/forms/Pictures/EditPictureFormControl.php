<?php

namespace App\Forms\Media;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditPictureFormControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit file information
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $image = $this->database->table('pictures')->get($this->getPresenter()->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('style', 'height: 200px;')
            ->setHtmlAttribute('class', 'form-control');
        $form->setDefaults([
            'id' => $image->id,
            'title' => $image->title,
            'description' => $image->description,
        ]);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('pictures')
            ->get($form->values->id)->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
            ]);

        $this->getPresenter()->redirect('this', ['id' => $form->values->id]);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/EditPictureFormControl.latte');
        $this->template->render();
    }
}
