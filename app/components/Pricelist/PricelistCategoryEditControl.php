<?php

namespace App\Forms\Pricelist;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PricelistCategoryEditControl extends Control
{

    /** @var Context */
    public $database;

    /**
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $categoryId = null;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/PricelistCategoryEditControl.latte');

        if ($this->presenter->getParameter('id')) {
            $categoryId = $this->presenter->getParameter('id');
        }

        $arr['parent_id'] = $categoryId;
        $arr['pricelist_lists_id'] = $this->presenter->getParameter('pricelist');

        $template->database = $this->database;
        $template->menuList = $this->database->table('pricelist_lists')->get($this->presenter->getParameter('pricelist'));
        $template->menu = $this->database->table('pricelist_categories')->where($arr)->order('sorted');
        $template->render();
    }

}
