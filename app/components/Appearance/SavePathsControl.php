<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;

class SavePathsControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Settings save
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('path_id');
        $form->addUpload("path", "dictionary.main.Image");
        $form->addSubmit('send', 'dictionary.main.Save');

        $form->onSuccess[] = $this->editSettingsSucceeded;
        return $form;
    }

    function editSettingsSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->path->name) < 1) {
            $this->presenter->redirect(this, array("id" => null));
        }

        if (file_exists($_FILES['path']['tmp_name']) || is_uploaded_file($_FILES['path']['tmp_name'])) {

            copy($_FILES['path']['tmp_name'], APP_DIR . '/images/paths/' . $form->values->path->name);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);

            if (file_exists(APP_DIR . '/www/images/paths/' . $form->values->path->name)) {
                \App\Model\IO::remove(APP_DIR . '/www/images/paths/' . $form->values->path->name);
            }

            $this->database->table("settings")->get($form->values->path_id)->update(array(
                "setvalue" => $form->values->path->name,
            ));

        }

        $this->redirect(this, array("id" => null));
    }

    public function render($item)
    {
        $template = $this->template;
        $template->item = $item;
        $template->appDir = $this->presenter->template->appDir;
        $template->setFile(__DIR__ . '/SavePathsControl.latte');

        $template->render();
    }

}
