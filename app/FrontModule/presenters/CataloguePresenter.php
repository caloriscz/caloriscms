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

        $this->template->menu = $this->database->table('store_categories')->where('parent_id', NULL);
    }

    /**
     * Finish order request
     */
    function createComponentAddToCartForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addHidden("stock");
        $form->addText("amount")
                ->setType("number")
                ->setAttribute("class", "form-control text-right");

        $form->addSubmit("submitm", "store.cart.add")
                ->setAttribute("class", "btn btn-success btn-sm");

        $form->onSuccess[] = $this->addToCartFormSucceeded;
        return $form;
    }

    function addToCartFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->user->isLoggedIn()) {
            $users_id = $this->user->getId();

            $storeDb = $this->database->table("cart")->where(array("users_id" => $users_id));

            if ($storeDb->count() == 0) {
                $storeId = $this->database->table("cart")->insert(array(
                    "users_id" => $users_id,
                    "uid" => $_COOKIE["PHPSESSID"],
                    "store_settings_shipping_id" => 3, // TODO: Get primary shipping method instead of guessing
                    "store_settings_payments_id" => 6, // TODO: Get primary payment method instead of guessing
                    "date_created" => date('Y-m-d H:i:s'),
                ));
            } else {
                $storeId = $storeDb->fetch()->id;

                $this->database->table("cart")->get($storeId)
                        ->update(array(
                            "date_created" => date('Y-m-d H:i:s'),
                ));
            }
        } else {
            $storeDb = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));

            if ($storeDb->count() == 0) {
                $storeId = $this->database->table("cart")->insert(array(
                    "users_id" => 0,
                    "uid" => $_COOKIE["PHPSESSID"],
                    "store_settings_shipping_id" => 3, // TODO: Get primary shipping method instead of guessing
                    "store_settings_payments_id" => 6, // TODO: Get primary payment method instead of guessing
                    "date_created" => date('Y-m-d H:i:s'),
                ));
            } else {
                $storeId = $storeDb->fetch()->id;

                $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]))
                        ->update(array(
                            "date_created" => date('Y-m-d H:i:s'),
                ));
            }

            $users_id = 0;
        }

        $productPrice = $this->database->table("store_stock")->where(array(
            "id" => $form->values->stock,
            "store_id" => $form->values->id,
        ));

        if ($productPrice->count() > 0) {
            $stock = $productPrice->fetch();
        } else {
            echo 'No stock found';
            exit();
        }

        $this->database->table("cart_items")->insert(array(
            "cart_id" => $storeId,
            "store_id" => $form->values->id,
            "store_stock_id" => $form->values->stock,
            "users_id" => $users_id,
            "amount" => $form->values->amount,
            "price" => $stock->price,
            "vat" => $stock->vat,
            "date_created" => date('Y-m-d H:i:s'),
        ));

        $this->redirect(':Front:Catalogue:detail', array("id" => $form->values->id));
    }

    /**
     * Sorting form
     */
    function createComponentSortingForm()
    {
        $sortCols = array(
            "dd" => "od nejnovějšího",
            "da" => "od nejstaršího",
            "pa" => "od nejlevnějšího",
            "pd" => "od nejdražšího",
            "na" => "A-Z",
            "nd" => "Z-A",
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
        $form->addHidden("brand");
        $form->addHidden("id");
        $form->addHidden("page");
        $form->addHidden("priceFrom");
        $form->addHidden("priceTo");
        $form->addHidden("src");
        $form->addHidden("user");
        $form->addSelect("o", 'Seřadit: ', $sortCols)
                ->setAttribute("class", "sortsel");
        $form->addSubmit("sort", 'Seřadit')
                ->setAttribute("class", "btn btn-primary btn-xs sortBtn")
                ->setAttribute("style", "display: none; height: 31px;");

        $form->setDefaults(array(
            "src" => $this->getParameter("src"),
            "brand" => $this->getParameter("brand"),
            "category" => $this->getParameter("id"),
            "o" => $sort,
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
        $this->redirect(":Front:Catalogue:default", $form->getValues(TRUE));
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

        $form->addSubmit('submitm', 'save')
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
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->setMethod("GET");
        $form->getElementPrototype()->class = "form-inline";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('idr', 'ID:');
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

        $form->addSubmit('submitm', 'save')
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
        $filter = new \App\Model\ProductFilter($this->database);
        $filter->order($this->getParameter("o"));
        $filter->setCategories($this->getParameter("id"));
        $filter->setManufacturer($this->getParameter("brand"));
        $filter->setUser($this->getParameter("user"));
        $filter->setText($this->getParameter("src"));
        $filter->setSize($this->getParameter("size"));
        $filter->setPrice($this->getParameter("priceFrom"), $this->getParameter("priceTo"));
        $filter->setParametres($this->getParameters());

        $assembleSQL = $filter->assemble();

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($assembleSQL->count("*"));
        $paginator->setItemsPerPage(6);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->store = $assembleSQL->order("id");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $assembleSQL->limit($paginator->getLength(), $paginator->getOffset());

        //Parametres
        $params = $this->database->table("store_param");
        $this->template->database = $this->database;
        $this->template->params = $params;
    }

    function renderBrands()
    {
        $brands = $this->database->table("store_brands");

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
        $this->template->store = $this->database->table("store")->get($this->getParameter("id"));
    }

}
