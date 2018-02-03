<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Utilities\ElfinderControl;


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