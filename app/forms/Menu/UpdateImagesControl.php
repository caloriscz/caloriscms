<?php
namespace App\Forms\Menu;

use Nette\Application\UI\Control;

class UpdateImagesControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentUpdateImagesForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('menu_id');
        $form->addUpload('the_file', 'dictionary.main.Image');
        $form->addUpload('the_file_2', 'Obrázek (hover)');
        $form->addUpload('the_file_3', 'Aktivní obrázek');
        $form->addUpload('the_file_4', 'Aktivní obrázek (hover)');

        $form->setDefaults(array("menu_id" => $this->presenter->getParameter("id")));

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'updateImagesFormSucceeded'];
        return $form;
    }

    public function updateImagesFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        /* Main image */
        if ($form->values->the_file->error == 0) {
            copy($_FILES["the_file"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . ".png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . ".png", 0644);
        }

        /* Hover image */
        if ($form->values->the_file_2->error == 0) {
            copy($_FILES["the_file_2"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_h.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_h.png", 0644);
        }

        /* Active image */
        if ($form->values->the_file_3->error == 0) {
            copy($_FILES["the_file_3"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_a.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_a.png", 0644);
        }

        /* Active hover image */
        if ($form->values->the_file_4->error == 0) {
            copy($_FILES["the_file_4"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_ah.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_ah.png", 0644);
        }


        $this->redirect(this, array("id" => $form->values->menu_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/UpdateImagesControl.latte');

        $template->render();
    }

}
