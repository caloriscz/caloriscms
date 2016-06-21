<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Product presenter.
 */
class ProductPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->menu = $this->database->table('categories')->where('parent_id', NULL);
    }

    function renderDefault()
    {
        $page = $this->database->table("pages")->get($this->getParameter("page_id"));
        $this->template->page = $this->database->table("pages")->get($page->id);


        $productDetailDb = $this->database->table("store")->where("pages.slug", $this->getParameter("slug"));
        $productDetail = $productDetailDb->fetch();

        $this->template->store = $productDetail;

        $categoryDb = $this->database->table("store_category")->where("pages_id", $productDetail->id)->fetch();
        $this->template->categoryId = $categoryDb->pages_id;
/*
        $this->template->parentCategory = $this->database->table("pages")->get($categoryDb->pages_id)->ref('pages', 'pages_id');

        $category = new Model\Category($this->database);

        $this->template->breadcrumbs = $category->getBreadcrumb($categoryDb->pages_id);
*/
        $this->template->database = $this->database;
    }

}
