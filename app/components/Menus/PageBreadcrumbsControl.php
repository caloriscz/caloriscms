<?php
namespace Caloriscz\Categories;

use App\Model\Category;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class PageBreadcrumbsControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $breadcrumb = new Category($this->database);
        $template->breadcrumbs = $breadcrumb->getPageBreadcrumb($this->presenter->getParameter('page_id'));
        $template->setFile(__DIR__ . '/PageBreadcrumbsControl.latte');
        $template->render();
    }

}
