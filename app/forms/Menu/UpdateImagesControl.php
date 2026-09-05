<?php

namespace App\Forms\Menu;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

class UpdateImagesControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
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
        $form->addUpload('the_file', 'Obrázek')
            ->addRule(Form::MIME_TYPE, 'Neplatny typ', IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_2', 'Obrázek (hover)')
            ->addRule(Form::MIME_TYPE, 'Neplatny typ', IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_3', 'Aktivní obrázek')
            ->addRule(Form::MIME_TYPE, 'Neplatny typ', IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_4', 'Aktivní obrázek (hover)')
            ->addRule(Form::MIME_TYPE, 'Neplatny typ', IO::IMAGE_MIME_TYPES);

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
        $menuId = (int) $form->values->menu_id;

        /* Main image */
        $this->saveMenuImage($form, 'the_file', APP_DIR . '/images/menu/' . $menuId . '.png');

        /* Hover image */
        $this->saveMenuImage($form, 'the_file_2', APP_DIR . '/images/menu/' . $menuId . '_h.png');

        /* Active image */
        $this->saveMenuImage($form, 'the_file_3', APP_DIR . '/images/menu/' . $menuId . '_a.png');

        /* Active hover image */
        $this->saveMenuImage($form, 'the_file_4', APP_DIR . '/images/menu/' . $menuId . '_ah.png');

        $this->redirect('this', ['id' => $menuId]);
    }

    private function saveMenuImage(BootstrapUIForm $form, string $field, string $targetFile): void
    {
        if ($form->values->{$field}->error === 0) {
            if (!IO::isAllowedImageUpload($_FILES[$field]['name'], $_FILES[$field]['tmp_name'])) {
                $this->flashMessage('Neplatny obrazek.', 'error');
                $this->redirect('this', ['id' => (int) $form->values->menu_id]);
            }

            copy($_FILES[$field]['tmp_name'], $targetFile);
            chmod($targetFile, 0644);
        }
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/UpdateImagesControl.latte');

        $template->render();
    }

}
