<?php
namespace App\Forms\Media;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditPictureFormControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
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
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('style', 'height: 200px;')
            ->setAttribute('class', 'form-control');
        $form->setDefaults([
            'id' => $image->id,
            'title' => $image->title,
            'description' => $image->description,
        ]);

        $form->addSubmit('send', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('pictures')
            ->get($form->values->id)->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
            ]);

        $this->getPresenter()->redirect('this', [
            'id' => $form->values->id,
        ]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditPictureFormControl.latte');
        $this->template->render();
    }
}
