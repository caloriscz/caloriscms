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
        $control->onSave[] = function (EditControl $control, $category) {
            $this->redirect('this');
        };

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
