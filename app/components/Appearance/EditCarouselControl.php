<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;

class EditCarouselControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit category
     */
    protected function createComponentEditForm()
    {
        $carousel = $this->database->table("carousel")->get($this->presenter->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('carousel_id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute("class", "form-control")
            ->setAttribute("style", "max-height: 150px;");
        $form->addText('uri', 'dictionary.main.URL');
        $form->addCheckbox("visible", "dictionary.main.Show");
        $form->addUpload('the_file', 'dictionary.main.Icon');
        $form->addSubmit('submitm', 'dictionary.main.Save');


        $arr = array(
            "carousel_id" => $carousel->id,
            "title" => $carousel->title,
            "description" => $carousel->description,
            "visible" => $carousel->visible,
            "uri" => $carousel->uri,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $image = $form->values->the_file->name;

        $arr = array(
            "title" => $form->values->title,
            "description" => $form->values->description,
            "uri" => $form->values->uri,
            "visible" => $form->values->visible,
        );

        if ($form->values->the_file->error == 0) {
            $arr['image'] = $image;

            if (file_exists(APP_DIR . "/images/carousel/" . $image)) {

                \App\Model\IO::remove(APP_DIR . "/images/carousel/" . $image);
                \App\Model\IO::upload(APP_DIR . "/images/carousel/", $image);
            } else {
                \App\Model\IO::upload(APP_DIR . "/images/carousel/", $image);
            }
        }

        $this->database->table("carousel")->get($form->values->carousel_id)
            ->update($arr);


        $this->redirect(this, array("carousel_id" => $form->values->carousel_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditCarouselControl.latte');

        $template->render();
    }

}
