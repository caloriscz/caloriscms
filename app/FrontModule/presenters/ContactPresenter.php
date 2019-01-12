<?php

namespace App\FrontModule\Presenters;


use Caloriscz\Page\ContactControl;

/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{
    protected function createComponentPageContact(): ContactControl
    {
        return new ContactControl($this->database);
    }
}
