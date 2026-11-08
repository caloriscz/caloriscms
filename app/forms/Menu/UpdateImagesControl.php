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
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_2', 'Obrázek (hover)')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_3', 'Aktivní obrázek')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
        $form->addUpload('the_file_4', 'Aktivní obrázek (hover)')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);

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

        try {
            (new \App\Model\AssetStorage($this->database, APP_DIR))->run(function ($assets) use ($menuId): void {
                if (!$this->database->table('menu')->get($menuId)) { throw new \Nette\Application\BadRequestException('Položka menu neexistuje.', 404); }
                foreach (['the_file' => '', 'the_file_2' => '_h', 'the_file_3' => '_a', 'the_file_4' => '_ah'] as $field => $suffix) {
                    $file = $_FILES[$field] ?? [];
                    if (\App\Model\AssetStorage::hasUpload($file)) { $assets->image($file, 'images/menu/' . $menuId . $suffix . '.png', true); }
                }
            });
        } catch (\Nette\Application\BadRequestException $error) {
            $this->presenter->flashMessage($error->getMessage(), 'error');
        }

        $this->redirect('this', ['id' => $menuId]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/UpdateImagesControl.latte');

        $template->render();
    }

}
