<?php
namespace App\Forms\Media;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditFileControl extends Control
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
     */
    protected function createComponentEditForm()
    {
        $image = $this->database->table('media')->get($this->getPresenter()->getParameter('id'));

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

        $form->onSuccess[] = [$this, "editFormSucceeded"];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('media')
            ->get($form->values->id)->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
            ]);

        $this->getPresenter()->redirect(this, array(
            'id' => $form->values->id,
        ));
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditFileFormControl.latte');
        $this->template->render();
    }

}
