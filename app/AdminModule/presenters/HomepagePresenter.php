<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Page\Editor\ElfinderControl;

/**
 * Admin homepage presenter.
 * @package App\AdminModule\Presenters
 */
class HomepagePresenter extends BasePresenter
{
    protected function createComponentElfinder()
    {
        return new ElfinderControl();
    }

}