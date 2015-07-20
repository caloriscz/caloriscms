<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni. Přihlaste se znovu.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

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
        $form->addText("setvalue", "Popisek:");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->editSettingsSucceeded;
        return $form;
    }

    function editSettingsSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        dump($values);

        foreach ($values["set"] as $key => $value) {
            $this->database->table('settings')->where(array(
                        "setkey" => $key,
                    ))
                    ->update(array(
                        "setvalue" => $value,
            ));
        }

        exit();
    }

    public function renderDefault()
    {
        $this->template->settingsDb = $this->database->table("settings");
    }

}
