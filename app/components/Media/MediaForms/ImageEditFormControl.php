<?php
namespace Caloriscz\Media\MediaForms;

use Nette\Application\UI\Control;

class ImageEditFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $image = $this->database->table("media")->get($this->presenter->getParameter("image"));

        $form->addHidden("image_id");
        $form->addHidden("page_id");
        $form->addHidden("name");
        $form->addCheckbox("detail_view", " Zobrazovat v galerii produktů");
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'dictionary.main.Save');

        $form->setDefaults(array(
            "image_id" => $this->presenter->getParameter("image"),
            "page_id" => $this->presenter->getParameter("id"),
            "name" => $this->presenter->getParameter("name"),
            "detail_view" => $image->detail_view,
            "description" => $image->description,
        ));

        $form->onSuccess[] = [$this, "editFormSucceeded"];
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("media")->get($form->values->image_id)
            ->update(array(
                'description' => $form->values->description,
                'detail_view' => $form->values->detail_view,
            ));

        $this->presenter->redirect(this, array(
            "id" => $form->values->page_id,
            "image" => $form->values->image_id,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ImageEditFormControl.latte');

        $template->render();
    }

}