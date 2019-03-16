<?php

namespace App\AdminModule\Presenters;

use App\Forms\Appearance\EditCarouselControl;
use App\Forms\Appearance\InsertCarouselControl;
use App\Forms\Appearance\SavePathsControl;
use Caloriscz\Appearance\CarouselManagerControl;

/**
 * Settings presenter.
 */
class AppearancePresenter extends BasePresenter
{

    protected function createComponentEditFormCarousel(): EditCarouselControl
    {
        return new EditCarouselControl($this->database);
    }

    protected function createComponentInsertFormCarousel(): InsertCarouselControl
    {
        return new InsertCarouselControl($this->database);
    }

    protected function createComponentSavePaths(): SavePathsControl
    {
        return new SavePathsControl($this->database);
    }

    protected function createComponentCarouselManager(): CarouselManagerControl
    {
        return new CarouselManagerControl($this->database);
    }

    public function renderDefault(): void
    {
        $arr = [
            'type' => 'local_path',
        ];

        $this->template->settingsDb = $this->database->table('settings')->where($arr);
    }

    public function renderCarouselDetail(): void
    {
        if ($this->getParameter('id')) {
            $this->template->carouselId = $this->getParameter('id');
        } else {
            $this->template->carouselId = false;
        }
    }

}
