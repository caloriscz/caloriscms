<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var \Caloriscz\Navigation\IDashboardControlFactory @inject */
    public $dashboardFactory;

    protected function createComponentDashboard()
    {
        $control = $this->dashboardFactory->create();
        return $control;
    }

    protected function createComponentElfinder()
    {
        $control = new \Caloriscz\Page\Editor\ElfinderControl;
        return $control;
    }

}