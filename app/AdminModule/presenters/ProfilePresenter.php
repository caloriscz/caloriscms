<?php

namespace App\AdminModule\Presenters;

/**
 * Import from files
 * @author Petr Karásek <caloris@caloris.cz>
 */
class ProfilePresenter extends BasePresenter
{
    /** @var \Caloriscz\Profile\Admin\IEditControlFactory @inject */
    public $editControlFactory;

    protected function createComponentEditProfile()
    {
        $control = $this->editControlFactory->create();
        $control->onSave[] = function ($error = false) {
            if ($error != false) {
                $this->flashMessage($error, "error");
            }

            $this->redirect('this');
        };

        return $control;
    }

    protected function createComponentProfileChangePassword()
    {
        $control = new \Caloriscz\Profile\ChangePasswordControl($this->database);
        return $control;
    }
}
