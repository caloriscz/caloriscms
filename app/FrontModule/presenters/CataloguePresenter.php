<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Catalogue presenter.
 */
class CataloguePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->menu = $this->database->table('categories')->where('parent_id', NULL);
    }

    /**
     * Sorting form
     */
    function createComponentSortingForm()
    {
        $sortCols = array(
            "dd" => "dictionary.sorting.new",
            "da" => "dictionary.sorting.old",
            "pa" => "dictionary.sorting.cheap",
            "pd" => "dictionary.sorting.expensive",
            "na" => "dictionary.sorting.az",
            "nd" => "dictionary.sorting.za",
        );

        if ($this->getParameter("o") == '') {
            $sort = 'dd';
        } else {
            $sort = $this->getParameter("o");
        }

        $form = new \Nette\Forms\FilterForm();
        $form->setMethod('GET');
        $form->getElementPrototype()->class = "form-horizontal form-sorting";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = 'simpleSort';
        $form->getElementPrototype()->id = 'order-me';
        $form->getElementPrototype()->onchange = 'document.getElementById("order-me").submit(); ';
        $form->setTranslator($this->translator);
        $form->addHidden("brand");
        $form->addHidden("id");
        $form->addHidden("page");
        $form->addHidden("priceFrom");
        $form->addHidden("priceTo");
        $form->addHidden("src");
        $form->addHidden("user");
        $form->addSelect("o", 'dictionary.main.Sort', $sortCols)
                ->setAttribute("class", "sortsel");
        $form->addSubmit("sort", 'Seřadit')
                ->setAttribute("class", "btn btn-primary btn-xs sortBtn")
                ->setAttribute("style", "display: none; height: 31px;");

        $form->setDefaults(array(
            "src" => $this->getParameter("src"),
            "brand" => $this->getParameter("brand"),
            "category" => $this->getParameter("id"),
            "o" => $this->translator->translate($sort),
            "priceFrom" => $this->getParameter("priceFrom"),
            "priceTo" => $this->getParameter("priceTo"),
            "page" => $this->getParameter("page"),
            "user" => $this->getParameter("user"),
        ));

        $form->onSuccess[] = $this->sortingFormSucceeded;
        return $form;
    }

    function sortingFormSucceeded($form = \Nette\Forms\FilterForm)
    {
        $filter = array_filter($form->getValues(TRUE));

        $this->redirect(":Front:Catalogue:default", $filter);
    }

    /**
     * Filter
     */
    protected function createComponentSearchForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->setMethod("GET");
        $form->getElementPrototype()->class = "form-inline";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('idr', 'ID:');
        $form->addText('src')
                ->setAttribute("class", "form-control")
                ->setAttribute("placeholder", \Nette\Utils\Strings::firstUpper("search"));
        $form->addText("priceFrom")
                ->setAttribute("style", "width: 50px;");
        $form->addText("priceTo")
                ->setAttribute("style", "width: 50px;");
        $form->addText("brand");

        if ($this->getParameter("id")) {
            $form->setDefaults(array(
                "idr" => $this->getParameter("id"),
            ));
        }

        $form->addSubmit('submitm', 'dictionary.main.Search')
                ->setAttribute("class", "btn btn-info btn-lg");


        $form->onSuccess[] = $this->searchFormSucceeded;
        return $form;
    }

    public function searchFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getValues(TRUE);

        unset($values["do"], $values["action"], $values["idr"]);
        $values["id"] = $form->values->idr;



        $this->redirect(":Front:Catalogue:default", $values);
    }

    /**
     * Param Filter
     */
    protected function createComponentParamSearchForm()
    {
        $form = $this->baseFormFactory->createPH();
        $form->setMethod("GET");

        $form->addHidden('idr');
        $form->addHidden('src');
        $form->addHidden("priceFrom");
        $form->addHidden("priceTo");
        $form->addHidden("brand");

        $form->setDefaults(array(
            "idr" => $this->getParameter("id"),
            "src" => $this->getParameter("src"),
            "priceFrom" => $this->getParameter("priceFrom"),
            "PriceTo" => $this->getParameter("priceTo"),
            "brand" => $this->getParameter("brand"),
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save')
                ->setAttribute("class", "btn btn-info btn-lg");


        $form->onSuccess[] = $this->paramSearchFormSucceeded;
        return $form;
    }

    public function paramSearchFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        unset($values["do"], $values["action"], $values["idr"]);

        $this->redirect(":Front:Catalogue:default", $values);
    }

    function renderDefault()
    {
        $catId = $this->getParameter("page_id");
        if ($catId) {
            $catByName = $this->database->table("pages")->get($catId);

            if ($catByName) {
                $category = $catByName;
            } else {
                $category = null;
            }
        } else {
            $category = null;
        }
        
        $this->template->category = category;

        $filter = new \App\Model\Store\Filter($this->database);
        $filter->order($this->getParameter("o"));
        $filter->setOptions($this->template->settings);
        $filter->setCategories($category);
        $filter->setManufacturer($this->getParameter("brand"));
        $filter->setUser($this->getParameter("user"));
        $filter->setText($this->getParameter("src"));
        $filter->setSize($this->getParameter("size"));
        $filter->setPrice($this->getParameter("priceFrom"), $this->getParameter("priceTo"));
        $filter->setParametres($this->getParameters());

        $assembleSQL = $filter->assemble();

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($assembleSQL->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->store = $assembleSQL->order("pages.id");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $assembleSQL->limit($paginator->getLength(), $paginator->getOffset());

        //Parametres
        $params = $this->database->table("param")->where(array(
            ":store_param_categories.pages_id" => $category,
        ));

        $this->template->database = $this->database;
        $this->template->params = $params;

        if ($this->getParameter("slug")) {
            $catName = new \App\Model\Category($this->database);
            $this->template->categoryName = $catName->getName($category);

            $this->template->breadcrumbs = $catName->getBreadcrumb($category);
        }
    }

    function renderBrands()
    {
        $brands = $this->database->table("contacts")
                ->where("categories_id", $this->template->settings['categories:id:contactsBrands']);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($brands->count("*"));
        $paginator->setItemsPerPage(6);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->store = $brands->order("title");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $brands->limit($paginator->getLength(), $paginator->getOffset());
    }

    function renderDetail()
    {
        $productDetail = $this->database->table("store")->where("slug.title", $this->getParameter("slug"))->fetch();

        $this->template->store = $productDetail;

        $categoryDb = $this->database->table("store_category")->where("store_id", $productDetail->id)->fetch();
        $this->template->categoryId = $categoryDb->categories_id;

        $this->template->parentCategory = $this->database->table("categories")->get($categoryDb->categories_id)->ref('categories', 'parent_id');

        $category = new Model\Category($this->database);

        $this->template->breadcrumbs = $category->getBreadcrumb($categoryDb->categories_id);

        $this->template->database = $this->database;
    }

}
