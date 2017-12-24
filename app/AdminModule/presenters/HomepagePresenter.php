<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Navigation\DashboardControl;
use Caloriscz\Page\Editor\ElfinderControl;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    protected function createComponentElfinder()
    {
        $control = new ElfinderControl();
        return $control;
    }

}