<?php

namespace App\AdminModule\Presenters;

use App\Forms\Lang\EditKeysControl;
use Caloriscz\Lang\LangListControl;

/**
 * Language presenter.
 */
class LangPresenter extends BasePresenter
{

    protected function createComponentEditKeys(): EditKeysControl
    {
        return new EditKeysControl($this->database);
    }

    protected function createComponentLangList(): LangListControl
    {
        return new LangListControl($this->database);
    }

    public function renderDefault(): void
    {
        $this->template->categoryId = null;
    }
}