<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class SitemapPresenter extends BasePresenter
{

    public function renderDefault(): void
    {
        $this->template->pages = $this->database->table('pages')->where(['public' => 1, 'sitemap' => 1]);
    }

}
