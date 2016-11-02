<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function createComponentHomepage()
    {
        $control = new \Caloriscz\Navigation\Homepage\HomepageControl($this->database);
        return $control;
    }
}
