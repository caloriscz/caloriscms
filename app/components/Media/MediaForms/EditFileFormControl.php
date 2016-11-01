<?php
namespace Caloriscz\Media\MediaForms;

use Nette\Application\UI\Control;

class EditFileControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit file information
     */
    function createComponentEditForm()
    {
        $image = $this->database->table("media")->get($this->presenter->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea('description', "dictionary.main.Description")
            ->setAttribute("style", "height: 200px;")
            ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "id" => $image->id,
            "title" => $image->title,
            "description" => $image->description,
        ));

        $form->addSubmit('send', 'dictionary.main.Save');

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("media")
            ->get($form->values->id)->update(array(
                'title' => $form->values->title,
                'description' => $form->values->description,
            ));


        $this->presenter->redirect(this, array(
            "id" => $form->values->id,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditFileFormControl.latte');

        $template->render();
    }

}
