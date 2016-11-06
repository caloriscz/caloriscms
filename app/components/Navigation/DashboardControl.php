<?php
namespace Caloriscz\Navigation;

use Nette\Application\UI\Control;

class DashboardControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        if ($this->presenter->template->settings['store:enabled']) {
            $template->orders = $this->database->table("orders")->where("orders_states_id NOT", null)->count();
            $template->carts = $this->database->table("orders")->where("orders_states_id", null)->count();

            $stats = new \App\Model\Store\Statistics($this->database);

            $statsParam = $this->presenter->getParameter('stats');

            if ($statsParam == 'td') {
                $arrTurnover = $stats->getData('d', 'price');
                $template->statsLabel = 'Obraty - denní';
            } elseif ($statsParam == 'am') {
                $arrTurnover = $stats->getData('m', 'amount');
                $this->presenter->template->statsLabel = 'Počty objednávek - měsíční';
            } elseif ($statsParam == 'ad') {
                $arrTurnover = $stats->getData('d', 'amount');
                $template->statsLabel = 'Počty objednávek - denní';
            } else {
                $arrTurnover = $stats->getData('m', 'price');
                $template->statsLabel = 'Obraty - denní';
            }

            $template->turnoverDays = $stats->convertKeysToString($arrTurnover);

            if (is_array($arrTurnover)) {
                $template->turnoverValues = implode(",", $arrTurnover);
            }

            $template->setFile(__DIR__ . '/DashboardControl.latte');

            $template->render();
        }
    }

}


interface IDashboardControlFactory
{
    /** @return \Caloriscz\Navigation\DashboardControl */
    function create();
}