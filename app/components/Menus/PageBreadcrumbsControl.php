<?php
namespace Caloriscz\Categories;

use App\Model\Category;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageBreadcrumbsControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $breadcrumb = new Category($this->database);
        $template->breadcrumbs = $breadcrumb->getPageBreadcrumb($this->presenter->getParameter('page_id'));
        $template->setFile(__DIR__ . '/PageBreadcrumbsControl.latte');
        $template->render();
    }

}
