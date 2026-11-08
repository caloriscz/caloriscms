<?php
namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

class SavePathsControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Settings save
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();

        $form->addHidden('path_id');
        $form->addUpload('path', 'Obrázek')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'editSettingsSucceeded'];
        return $form;
    }

    /**
     * Edit setting for path
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editSettingsSucceeded(BootstrapUIForm $form): void
    {

        $file = $_FILES['path'] ?? [];
        try {
            if (\App\Model\AssetStorage::hasUpload($file)) {
                (new \App\Model\AssetWrites($this->database, APP_DIR))->setting((int) $form->values->path_id, $file);
            }
        } catch (\Nette\Application\BadRequestException $error) {
            $this->presenter->flashMessage($error->getMessage(), 'error');
        }
        $this->redirect('this', ['id' => null]);

    }

    public function render($item): void
    {
        $this->template->item = $item;
        $this->template->appDir = $this->presenter->template->appDir;
        $this->template->setFile(__DIR__ . '/SavePathsControl.latte');
        $this->template->render();
    }

}
