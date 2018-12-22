<?php

namespace App\AdminModule\Presenters;

use App\Forms\Profile\ChangePasswordControl;
use Apps\Forms\Profile\EditControl;

/**
 * User profile presenter
 * @package App\AdminModule\Presenters
 */
class ProfilePresenter extends BasePresenter
{
    protected function createComponentEditProfile(): EditControl
    {
        $control = new EditControl($this->database);
        $control->onSave[] = function ($error = false) {
            if ($error !== false) {
                $this->flashMessage($error, 'error');
            }

            $this->redirect('this');
        };

        return $control;
    }

    protected function createComponentProfileChangePassword(): ChangePasswordControl
    {
        return new ChangePasswordControl($this->database);
    }
}
