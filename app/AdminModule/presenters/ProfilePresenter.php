<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Profile\ChangePasswordControl;

/**
 * User profile presenter
 * @package App\AdminModule\Presenters
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
                $this->flashMessage($error, 'error');
            }

            $this->redirect('this');
        };

        return $control;
    }

    protected function createComponentProfileChangePassword()
    {
        return new ChangePasswordControl($this->database);
    }
}
