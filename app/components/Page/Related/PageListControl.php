<?php
namespace Caloriscz\Page\Related;

use App\Model\Store\Filter;
use Caloriscz\Page\Pages\PageItemControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentProduct()
    {
        $control = new PageItemControl($this->database);
        return $control;
    }

    public function render($page)
    {
        $template = $this->getTemplate();

        $filter = new Filter($this->database);
        $filter->setOptions($this->presenter->template->settings);

        $arr = [
            ':pages_related.related_pages_id' => 511,
            'pages_types_id' => 4
        ];


        $filter->setColumns($arr, $limit = 4);
        $filter->setParametres($this->getParameters());

        $assembleSQL = $filter->assemble();

        $template->categoryArr = $this->presenter->getParameters();
        $template->store = $assembleSQL->order('pages.id')->limit($limit);
        $template->setFile(__DIR__ . '/PageListControl.latte');
        $template->render();
    }

}