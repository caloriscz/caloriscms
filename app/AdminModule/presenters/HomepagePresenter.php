<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if ($this->template->settings['store:enabled']) {
            $this->template->orders = $this->database->table("orders")->where("orders_states_id NOT", null)->count();
            $this->template->carts = $this->database->table("orders")->where("orders_states_id", null)->count();

            $stats = new Model\Store\Statistics($this->database);

            $statsParam = $this->getParameter('stats');

            if ($statsParam == 'td') {
                $arrTurnover = $stats->getData('d', 'price');
                $this->template->statsLabel = 'Obraty - denní';
            } elseif ($statsParam == 'am') {
                $arrTurnover = $stats->getData('m', 'amount');
                $this->template->statsLabel = 'Počty objednávek - měsíční';
            } elseif ($statsParam == 'ad') {
                $arrTurnover = $stats->getData('d', 'amount');
                $this->template->statsLabel = 'Počty objednávek - denní';
            } else {
                $arrTurnover = $stats->getData('m', 'price');
                $this->template->statsLabel = 'Obraty - denní';
            }

            $this->template->turnoverDays = $stats->convertKeysToString($arrTurnover);

            if (is_array($arrTurnover)) {
                $this->template->turnoverValues = implode(",", $arrTurnover);
            }
        }
    }

}
