<?php
namespace App\Forms\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class SavePathsControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Settings save
     */
    protected function createComponentEditForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);

        $form->addHidden('path_id');
        $form->addUpload('path', 'dictionary.main.Image');
        $form->addSubmit('send', 'dictionary.main.Save');

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
        if ('' === $form->values->path->name) {
            $this->presenter->redirect('this', ['id' => null]);
        }

        if (file_exists($_FILES['path']['tmp_name']) || is_uploaded_file($_FILES['path']['tmp_name'])) {

            copy($_FILES['path']['tmp_name'], APP_DIR . '/images/paths/' . $form->values->path->name);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);

            if (file_exists(APP_DIR . '/www/images/paths/' . $form->values->path->name)) {
                IO::remove(APP_DIR . '/www/images/paths/' . $form->values->path->name);
            }

            $this->database->table('settings')->get($form->values->path_id)->update([
                'setvalue' => $form->values->path->name,
            ]);

        }

        $this->redirect('this', ["id" => null]);
    }

    public function render($item)
    {
        $this->template->item = $item;
        $this->template->appDir = $this->presenter->template->appDir;
        $this->template->setFile(__DIR__ . '/SavePathsControl.latte');
        $this->template->render();
    }

}
