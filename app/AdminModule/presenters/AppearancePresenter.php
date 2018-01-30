<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Appearance\CarouselManagerControl;
use Caloriscz\Appearance\CarouselGridControl;
use Caloriscz\Appearance\EditCarouselControl;
use Caloriscz\Appearance\InsertCarouselControl;
use Caloriscz\Appearance\SavePathsControl;
use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class AppearancePresenter extends BasePresenter
{

    protected function createComponentEditFormCarousel()
    {
        return new EditCarouselControl($this->database);
    }

    protected function createComponentInsertFormCarousel()
    {
        return new InsertCarouselControl($this->database);
    }

    protected function createComponentSavePaths()
    {
        return new SavePathsControl($this->database);
    }

    protected function createComponentCarouselManager()
    {
        return new CarouselManagerControl($this->em);
    }

    public function renderDefault()
    {
        $arr = [
            'settings_categories_id' => 20,
            'type' => 'local_path',
        ];

        $this->template->settingsDb = $this->database->table('settings')->where($arr);
    }

    public function renderCarouselDetail()
    {
        if ($this->getParameter('id')) {
            $this->template->carouselId = $this->getParameter('id');
        } else {
            $this->template->carouselId = false;
        }
    }

}
