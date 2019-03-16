<?php

namespace App\Forms\Menu;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class UpdateImagesControl extends Control
{

    /** @var Context */
    public $database;

    /**
     * UpdateImagesControl constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentUpdateImagesForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('menu_id');
        $form->addUpload('the_file', 'Obrázek');
        $form->addUpload('the_file_2', 'Obrázek (hover)');
        $form->addUpload('the_file_3', 'Aktivní obrázek');
        $form->addUpload('the_file_4', 'Aktivní obrázek (hover)');

        $form->setDefaults(['menu_id' => $this->presenter->getParameter('id')]);

        $form->addSubmit('submitm', 'Uložit');

        $form->onSuccess[] = [$this, 'updateImagesFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function updateImagesFormSucceeded(BootstrapUIForm $form): void
    {
        /* Main image */
        if ($form->values->the_file->error === 0) {
            copy($_FILES['the_file']['tmp_name'], APP_DIR . '/images/menu/' . $form->values->menu_id . '.png');
            chmod(APP_DIR . '/images/menu/' . $form->values->menu_id . '.png', 0644);
        }

        /* Hover image */
        if ($form->values->the_file_2->error === 0) {
            copy($_FILES['the_file_2']['tmp_name'], APP_DIR . '/images/menu/' . $form->values->menu_id . '_h.png');
            chmod(APP_DIR . '/images/menu/' . $form->values->menu_id . '_h.png', 0644);
        }

        /* Active image */
        if ($form->values->the_file_3->error === 0) {
            copy($_FILES['the_file_3']['tmp_name'], APP_DIR . '/images/menu/' . $form->values->menu_id . '_a.png');
            chmod(APP_DIR . '/images/menu/' . $form->values->menu_id . '_a.png', 0644);
        }

        /* Active hover image */
        if ($form->values->the_file_4->error === 0) {
            copy($_FILES['the_file_4']['tmp_name'], APP_DIR . '/images/menu/' . $form->values->menu_id . '_ah.png');
            chmod(APP_DIR . '/images/menu/' . $form->values->menu_id . '_ah.png', 0644);
        }

        $this->redirect('this', ['id' => $form->values->menu_id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/UpdateImagesControl.latte');

        $template->render();
    }

}
