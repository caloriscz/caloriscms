<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    /**
     * Settings save
     */
    function createComponentEditSettingsForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('setkey');
        $form->addText("setvalue", "dictionary.main.Description");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->editSettingsSucceeded;
        return $form;
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

        $this->redirect(":Admin:Settings:default", array("id" => null));
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:settings'];

        if (!$this->getParameter("id")) {
            $arr = array(
                "admin_editable" => 1,
            );
        } else {
            $arr = array(
                "admin_editable" => 1,
                "categories_id" => $this->getParameter("id"),
            );
        }

        $this->template->settingsDb = $this->database->table("settings")
                ->where($arr);
    }

}
