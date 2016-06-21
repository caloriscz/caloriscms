<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Blog presenter.
 */
class BlogPresenter extends BasePresenter
{

    function renderDefault()
    {
        $blog = $this->database->table("pages")->order("doc.date_created DESC");

        $this->template->items = $blog->limit(20);
    }

}
