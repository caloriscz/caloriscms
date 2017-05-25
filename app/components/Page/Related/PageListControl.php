<?php
namespace Caloriscz\Page\Related;

use Nette\Application\UI\Control;

class PageListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentProduct()
    {
        $control = new \Caloriscz\Store\Product\ProductControl($this->database);
        return $control;
    }

    public function render($page)
    {
        $template = $this->template;

        $filter = new \App\Model\Store\Filter($this->database);
        $filter->setOptions($this->presenter->template->settings);

        $arr = array(
            ":pages_related.related_pages_id" => 511,
            "pages_types_id" => 4
        );


        $filter->setColumns($arr, $limit = 4);
        $filter->setParametres($this->getParameters());

        $assembleSQL = $filter->assemble();

        $template->categoryArr = $this->presenter->getParameters();
        $template->store = $assembleSQL->order("pages.id")->limit($limit);

        $template->setFile(__DIR__ . '/PageListControl.latte');

        $template->render();
    }

}