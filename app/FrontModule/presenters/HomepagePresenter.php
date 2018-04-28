<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Page\HomepageControl;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /**
     * @return HomepageControl
     */
    protected function createComponentHomepage(): HomepageControl
    {
        return new HomepageControl($this->database);
    }

    public function renderDefault(): void
    {
        $this->template->page = $this->database->table('pages')->get(1);
    }
}
