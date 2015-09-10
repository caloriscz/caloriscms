<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class CataloguePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }

        $this->template->brands = $this->database->table("store_brands")
                        ->order("title")->fetchPairs("id", "title");
        $this->template->vats = $this->database->table("store_settings_vats")->where(array("show" => 1))
                        ->order("title")->fetchPairs("id", "vat");
    }

    /**
     * Insert product
     */
    protected function createComponentInsertProductForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id', 'ID:');
        $form->addText('title', 'name');
        $form->addSubmit('submitm', 'save');

        $form->onSuccess[] = $this->insertProductFormSucceeded;
        return $form;
    }

    public function insertProductFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {

        $id = $this->database->table("store")->insert(array(
            "title" => $form->values->title,
            "date_created" => date("Y-m-d H:i:s"),
        ));

        Model\IO::directoryMake(APP_DIR . '/images/catalogue/products/' . Nette\Utils\Strings::padLeft($id, 6, '0'), 0755);

        $this->redirect(":Admin:Catalogue:detail", array("id" => $id));
    }

    /**
     * Edit product
     */
    protected function createComponentEditForm()
    {
        $catalogue = $this->database->table("store")
                ->get($this->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id', 'ID:');
        $form->addText('title', 'Name');
        $form->addSelect("brand", "Brand", $this->template->brands)
                ->setAttribute("class", "form-control")
                ->setTranslator(NULL);
        $form->addTextarea('description', 'description')
                ->setAttribute("class", "form-control")
                ->setHtmlId("wysiwyg");

        $form->addSubmit('submitm', 'Save');

        $form->setDefaults(array(
            "id" => $catalogue->id,
            "title" => $catalogue->title,
            "brand" => $catalogue->store_brands_id,
            "description" => $catalogue->description,
        ));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {

        $this->database->table("store")->get($form->values->id)
                ->update(array(
                    "title" => $form->values->title,
                    "description" => $form->values->description,
                    "store_brands_id" => $form->values->brand,
                    "date_modified" => date("Y-m-d H:i:s"),
        ));

        $this->redirect(":Admin:Catalogue:detail", array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertParameterForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $paramsDb = $this->database->table("store_param");

        foreach ($paramsDb as $param) {
            $params .= $param->{'param_' . $this->template->langSelected} . ';';
        }

        $form->addHidden('id', 'ID:');
        $form->addText('group', 'group');
        $form->addText('paramkey', 'parameter')
                ->setAttribute("data-params", rtrim($params, ";"));
        $form->addText('paramvalue', 'value');
        $form->addSubmit('submitm', 'Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->insertParameterFormSucceeded;
        return $form;
    }

    public function insertParameterFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        // Check id for name - if it doesn't exist, create it
        $paramDb = $this->database->table("store_param")->where("param_cs = ? OR param_en = ?", $form->values->paramkey, $form->values->paramkey);

        if ($paramDb->count() == 0) {
            $paramNew = $this->database->table("store_param")->insert(array(
                "param_en" => $form->values->paramkey,
                "param_cs" => $form->values->paramkey,
            ));
            $paramId = $paramNew;
        } else {
            $paramId = $paramDb->fetch()->id;
        }

        $this->database->table("store_params")->insert(array(
            "store_id" => $form->values->id,
            "group" => $form->values->group,
            "store_param_id" => $paramId,
            "paramvalue" => $form->values->paramvalue,
        ));

        $this->redirect(":Admin:Catalogue:detailParams", array("id" => $form->values->id));
    }

    function handleDeleteParam($id)
    {
        $this->database->table('store_params')->get($id)->delete();
        $this->redirect(":Admin:Catalogue:detailParams", array("id" => $this->getParameter("product")));
    }

    /**
     * Category edit
     */
    function createComponentEditCategoryForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->id = "search-form";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->editCategoryFormSucceeded;
        return $form;
    }

    function editCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT, 'catg[]'); // get value from html input

        $this->database->table("store_category")->where(array("store_id" => $form->values->id))->delete();

        foreach ($values as $value) {
            $this->database->table("store_category")->insert(array(
                "store_id" => $form->values->id,
                "store_categories_id" => $value,
            ));
        }

        $this->redirect(':Admin:Catalogue:detail', array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertStockForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addText('title', 'Title');
        $form->addText('price', 'Price');
        $form->addText('amount', 'Amount');

        if ($this->template->settings["vat_payee"] == 1) {
            $form->addSelect('vat', 'VAT', $this->template->vats)
                    ->setAttribute("class", "form-control")
                    ->setTranslator(NULL);
        }

        $form->addSubmit('submitm', 'Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "amount" => 0,
        ));

        $form->onSuccess[] = $this->insertStockFormSucceeded;
        return $form;
    }

    public function insertStockFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->template->settings["vat_payee"] == 1) {
            $vat = $form->values->vat;
        } else {
            $vat = 0;
        }

        $this->database->table("store_stock")->insert(array(
            "store_id" => $form->values->id,
            "title" => $form->values->title,
            "price" => $form->values->price,
            "amount" => $form->values->amount,
            "store_settings_vats_id" => $vat,
        ));

        $this->redirect(":Admin:Catalogue:detailStock", array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertBrandForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'Title');
        $form->addSubmit('submitm', 'Insert');

        $form->onSuccess[] = $this->insertBrandFormSucceeded;
        return $form;
    }

    public function insertBrandFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_brands")->insert(array(
            "title" => $form->values->title,
        ));

        $this->redirect(":Admin:Catalogue:brands");
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertNewParamForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator->domain('dictionary.main'));
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('param', 'Title');
        $form->addSubmit('submitm', 'Insert');

        $form->onSuccess[] = $this->insertNewParamFormSucceeded;
        return $form;
    }

    public function insertNewParamFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_param")->insert(array(
            "param" => $form->values->param,
        ));

        $this->redirect(":Admin:Catalogue:parametres");
    }

    /**
     * Image Upload
     */
    function createComponentUploadForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden("id");
        $form->addUpload('the_file', 'Vložit obrázek:')
                ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Invalid type of image:', $imageTypes);
        $form->addTextarea("description", "Description")
                ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $thumbName = 'tn_';
        $album = \Nette\Utils\Strings::padLeft($form->values->id, 6, '0');
        $fileDirectory = APP_DIR . '/images/catalogue/products/' . $album . '/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $imageExists = $this->database->table('store_images')->where(array(
                'filename' => $_FILES["the_file"]["name"],
                'store_id' => $form->values->id,
            ));

            if ($imageExists->count() == 0) {
                $this->database->table('store_images')->insert(array(
                    'filename' => $_FILES["the_file"]["name"],
                    'store_id' => $form->values->id,
                    'description' => $form->values->description,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
            }

            $fileName = $fileDirectory . $_FILES["the_file"]["name"];
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);

            // thumbnails
            $image = \Nette\Utils\Image::fromFile($fileName);
            $image->resize(400, 250, \Nette\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/images/catalogue/products/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"]);
            chmod(APP_DIR . '/images/catalogue/products/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"], 0777);
        }

        $this->redirect(":Admin:Catalogue:detailImages", array(
            "id" => $form->values->id,
            "category" => $form->values->category,
        ));
    }

    function handleDeleteStock($id)
    {
        $this->database->table('store_stock')->get($id)->delete();
        $this->redirect(":Admin:Catalogue:detailStock", array("id" => $this->getParameter("product")));
    }

    /**
     * Delete image
     */
    function handleDeleteImage($id)
    {
        $this->database->table('store_images')->get($id)->delete();

        \App\Model\IO::remove(APP_DIR . '/images/catalogue/products/' . \Nette\Utils\Strings::padLeft($id, 6, '0') . '/' . $this->getParameter("name"));
        \App\Model\IO::remove(APP_DIR . '/images/catalogue/products/' . \Nette\Utils\Strings::padLeft($id, 6, '0') . '/tn_' . $this->getParameter("name"));

        $this->redirect(":Admin:Catalogue:detailImages", array("id" => $id,));
    }

    public function renderDefault()
    {
        $this->template->catalogue = $this->database->table("store")->order("title");
    }

    public function renderDetail()
    {
        $this->template->catalogue = $this->database->table("store")->get($this->getParameter("id"));

        $this->template->stock = $this->database->table("store_stock")
                ->where(array("store_id" => $this->getParameter("id")));


        $this->template->menu = $this->database->table('store_categories')->where('parent_id', NULL);
        $categoriesSelected = $this->database->table('store_category')->where(array(
            'store_id' => $this->getParameter("id"),
        ));

        foreach ($categoriesSelected as $arr) {
            $categories[] = $arr->store_categories_id;
        }

        $this->template->categories = $categories;

        //echo print_r($this->template->categoriesSelected);
    }

    public function renderDetailParams()
    {
        $this->template->catalogue = $this->database->table("store")->get($this->getParameter("id"));
        $this->template->params = $this->database->table("store_params")
                ->where(array("store_id" => $this->getParameter("id")));
    }

    public function renderDetailStock()
    {
        $this->template->catalogue = $this->database->table("store")->get($this->getParameter("id"));
        $this->template->stock = $this->database->table("store_stock")
                ->where(array("store_id" => $this->getParameter("id")));
    }

    public function renderDetailImages()
    {
        $this->template->catalogue = $this->database->table("store")->get($this->getParameter("id"));
        $this->template->images = $this->database->table("store_images")
                ->where(array("store_id" => $this->getParameter("id")));
    }

    function renderBrands()
    {
        $brands = $this->database->table("store_brands");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($brands->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->store = $brands->order("title");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $brands->limit($paginator->getLength(), $paginator->getOffset());
    }

    function renderParametres()
    {
        $params = $this->database->table("store_param");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($params->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->store = $params->order("param");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $params->limit($paginator->getLength(), $paginator->getOffset());
    }

}
