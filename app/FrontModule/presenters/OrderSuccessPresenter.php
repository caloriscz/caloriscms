<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Cart presenter.
 */
class OrderSuccessPresenter extends BasePresenter
{
    function renderDefault()
    {
        $transId = (int)substr($this->getParameter("transid"), 6);

        $residencyDb = $this->database->table("contacts")->get($this->template->settings['contacts:residency:contacts_id']);

        if ($residencyDb) {
            $this->template->residency = $residencyDb;
        } else {
            $this->template->residency = false;
        }

        $order = $this->database->table("orders")->get($transId);
        $this->template->order = $order;
    }

}
