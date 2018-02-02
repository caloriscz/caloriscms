<?php

namespace App\AdminModule\Presenters;

use Apps\Forms\Profile\EditControl;
use Caloriscz\Profile\ChangePasswordControl;

/**
 * User profile presenter
 * @package App\AdminModule\Presenters
 */
class ProfilePresenter extends BasePresenter
{
    protected function createComponentEditProfile()
    {
        $control = new EditControl($this->database);
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
