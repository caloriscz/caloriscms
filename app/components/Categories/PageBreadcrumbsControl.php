<?php
namespace Caloriscz\Categories;

use Nette\Application\UI\Control;

class PageBreadcrumbsControl extends Control
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

        $breadcrumb = new \App\Model\Category($this->database);
        $template->breadcrumbs = $breadcrumb->getPageBreadcrumb($this->presenter->getParameter("page_id"));

        $template->setFile(__DIR__ . '/PageBreadcrumbsControl.latte');

        $template->render();
    }

}
