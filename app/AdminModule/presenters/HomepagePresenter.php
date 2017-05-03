<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Navigation\DashboardControl;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    protected function createComponentElfinder()
    {
        $control = new \Caloriscz\Page\Editor\ElfinderControl;
        return $control;
    }

}