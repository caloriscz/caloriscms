<?php
namespace Caloriscz\Settings;

use Nette\Application\UI\Control;

class EditSettingsControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentEditSettingsForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('category_id');
        $form->addHidden('setkey');
        $form->addText("setvalue", "dictionary.main.Description");

        $arr = array_filter(array("category_id" => $this->presenter->getParameter("id")));

        $form->setDefaults($arr);

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->editSettingsSucceeded;
        $form->onValidate[] = $this->permissionValidated;
        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->presenter->template->member->users_roles->settings_edit == 0) {
            $this->presenter->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function editSettingsSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        foreach ($values["set"] as $key => $value) {
            $this->database->table("settings")->where(array(
                "setkey" => $key,
            ))
                ->update(array(
                    "setvalue" => $value,
                ));
        }

        $this->presenter->redirect(this, array("id" => $form->values->category_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->langSelected = $this->presenter->translator->getLocale();

        if (!$this->getParameter("id")) {
            $arr = array(
                "admin_editable" => 1,
                "categories_id" => 10,
            );
        } else {
            $arr = array(
                "admin_editable" => 1,
                "categories_id" => $this->getParameter("id"),
            );
        }

        $this->template->database = $this->database;

        $template->settingsDb = $this->database->table("settings")
            ->where($arr);
        $template->setFile(__DIR__ . '/EditSettingsControl.latte');

        $template->render();
    }

}