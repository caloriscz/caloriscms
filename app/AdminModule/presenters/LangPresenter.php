<?php

namespace App\AdminModule\Presenters;

use App\Forms\Settings\EditSettingsControl;
use App\Forms\Settings\InsertCountryControl;
use App\Forms\Settings\InsertCurrencyControl;
use App\Forms\Settings\InsertLanguageControl;
use Caloriscz\Settings\SettingsCategoriesControl;
use Nette\Application\AbortException;

/**
 * Language presenter.
 */
class LangPresenter extends BasePresenter
{

    protected function createComponentEditSettings(): EditSettingsControl
    {
        return new EditSettingsControl($this->database);
    }

    protected function createComponentSettingsCategories(): SettingsCategoriesControl
    {
        return new SettingsCategoriesControl($this->database);
    }

    public function renderDefault(): void
    {
        $this->template->categoryId = null;
    }
}