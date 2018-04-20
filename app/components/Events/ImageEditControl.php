<?php
namespace Caloriscz\Events;

use Nette\Application\UI\Control;

class ImageEditControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Image Upload
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $image = $this->database->table("media")->get($this->presenter->getParameter("name"));

        $form->addHidden("id");
        $form->addHidden("name");
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->presenter->getParameter("id"),
            "name" => $this->presenter->getParameter("name"),
            "description" => $image->description,
        ));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("media")->get($form->values->name)
            ->update(array(
                'description' => $form->values->description,
            ));


        $this->presenter->redirect(this, array(
            "id" => $form->values->id,
            "name" => $form->values->name,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ImageEditControl.latte');

        $template->render();
    }

}