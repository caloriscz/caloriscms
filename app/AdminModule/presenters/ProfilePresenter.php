<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Import from files
 * @author Petr Karásek <caloris@caloris.cz>
 */
class ProfilePresenter extends BasePresenter
{
    protected function createComponentEditProfile()
    {
        $control = new \Caloriscz\Profile\Admin\EditControl($this->database);
        return $control;
    }

    protected function createComponentProfileChangePassword()
    {
        $control = new \Caloriscz\Profile\ChangePasswordControl($this->database);
        return $control;
    }

    protected function createComponentLanguageSelectorForAdmin()
    {
        $control = new \Caloriscz\Settings\Language\LanguageSelectorForAdminControl($this->database);
        return $control;
    }
}
