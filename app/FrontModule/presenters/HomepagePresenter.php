<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    public function renderDefault()
    {
        $filter = new \App\Model\Store\Filter($this->database);
        $filter->order('sd');
        $filter->setOptions($this->template->settings);
        $filter->setManufacturer($this->getParameter("brand"));
        $filter->setUser($this->getParameter("user"));
        $filter->setText($this->getParameter("src"));
        $filter->setSize($this->getParameter("size"));
        $filter->setPrice($this->getParameter("priceFrom"), $this->getParameter("priceTo"));
        $filter->setParametres($this->getParameters());

        $this->template->store = $filter->assemble()->limit(4, 0);
        $this->template->snippets = $this->database->table("snippets")->where("pages_id", 1)->fetchPairs('id', 'content');
    }

}
