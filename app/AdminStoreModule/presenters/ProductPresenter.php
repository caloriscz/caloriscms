<?php

namespace App\AdminStoreModule\Presenters;

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

        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));

        $this->template->brands = $this->database->table("contacts")
            ->where("categories_id", $this->template->settings['categories:id:contactsBrands'])
            ->order("company")->fetchPairs("id", "company");
        $this->template->vats = $this->database->table("store_settings_vats")->where(array("show" => 1))
            ->order("title")->fetchPairs("id", "vat");
    }

    /**
     * Edit product
     */
    protected function createComponentEditForm()
    {
        $page = $this->database->table("pages")
            ->get($this->getParameter("id"));
        $storeDb = $this->database->table("store")->where(array("pages_id" => $page->id));
        $store = $storeDb->fetch();

        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addHidden('store_id');
        $form->addText('price', 'dictionary.main.Price');
        $form->addSelect("vat", "dictionary.main.VAT", $this->template->vats)
            ->setAttribute("class", "form-control");
        $form->addSelect("brand", "dictionary.main.Brand", $this->template->brands)
            ->setAttribute("class", "form-control")
            ->setOption("description", \Nette\Utils\Html::el('a', 'přidat novou značku')->href('/admin/contacts/default/169'));
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults(array(
            "id" => $page->id,
            "store_id" => $store->id,
            "brand" => $store->contacts_id,
            "price" => $store->price,
            "vat" => $store->store_settings_vats_id,
        ));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store")->get($form->values->store_id)
            ->update(array(
                "price" => $form->values->price,
                "contacts_id" => $form->values->brand,
                "store_settings_vats_id" => $form->values->vat,
            ));

        $this->redirect(":AdminStore:Product:default", array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertParameterForm()
    {
        $form = $this->baseFormFactory->createUI();
        $paramsDb = $this->database->table("param");

        foreach ($paramsDb as $param) {
            $params .= $param->{'param_' . $this->template->langSelected} . ';';
        }

        $form->addHidden('id', 'ID');
        $form->addText('group', 'dictionary.main.Group');
        $form->addText('paramkey', 'dictionary.main.Parameter')
            ->setAttribute("data-params", rtrim($params, ";"));
        $form->addText('paramvalue', 'dictionary.main.Value');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->insertParameterFormSucceeded;
        return $form;
    }

    public function insertParameterFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        // Check id for name - if it doesn't exist, create it
        $paramDb = $this->database->table("param")->where("param_cs = ? OR param_en = ?", $form->values->paramkey, $form->values->paramkey);

        if ($paramDb->count() == 0) {
            $paramNew = $this->database->table("param")->insert(array(
                "param_en" => $form->values->paramkey,
                "param_cs" => $form->values->paramkey,
            ));
            $paramId = $paramNew;
        } else {
            $paramId = $paramDb->fetch()->id;
        }

        $this->database->table("params")->insert(array(
            "pages_id" => $form->values->id,
            "group" => $form->values->group,
            "param_id" => $paramId,
            "paramvalue" => $form->values->paramvalue,
        ));

        $this->redirect(":AdminStore:Product:params", array("id" => $form->values->id));
    }

    function handleDeleteParam($id)
    {
        $this->database->table("params")->get($id)->delete();
        $this->redirect(":AdminStore:Product:params", array("id" => $this->getParameter("product")));
    }

    /**
     * Category edit
     */
    function createComponentEditCategoryForm()
    {
        $form = $this->baseFormFactory->createUI();
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

        $this->database->table("store_category")->where(array("pages_id" => $form->values->id))->delete();

        foreach ($values as $value) {
            $this->database->table("store_category")->insert(array(
                "pages_id" => $form->values->id,
                "category_pages_id" => $value,
            ));
        }

        $this->redirect(':AdminStore:Product:default', array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertStockForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addText('partnr', 'ID');
        $form->addText('weight', 'dictionary.main.Weight');
        $form->addText('amount', 'dictionary.main.Amount');

        if ($this->template->settings["site:vat:payee"] == 1) {

        }

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "amount" => $this->template->settings["store:stock:amountDefault"]
        ));

        $form->onSuccess[] = $this->insertStockFormSucceeded;
        return $form;
    }

    public function insertStockFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->group == 0) {
            $categoryId = null;
        } else {
            $categoryId = $form->values->group;
        }

        $this->database->table("stock")->insert(array(
            "pages_id" => $form->values->id,
            "title" => $form->values->title,
            "partnr" => $form->values->partnr,
            "weight" => $form->values->weight,
            "amount" => $form->values->amount,
        ));

        $this->redirect(":AdminStore:Product:stock", array("id" => $form->values->id));
    }

    /**
     * Insert stock
     */
    protected function createComponentEditStockForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addHidden('prodid');
        $form->addText('title', 'dictionary.main.Title')
            ->setAttribute("class", "form-control");
        $form->addText('partnr', 'ID')
            ->setAttribute("class", "form-control");
        $form->addText('price', 'dictionary.main.Price')
            ->setAttribute("class", "form-control");
        $form->addText('amount', 'dictionary.main.Amount')
            ->setAttribute("class", "form-control")
            ->setType('number')
            ->setDefaultValue(1)
            ->addRule(\Nette\Forms\Form::INTEGER, 'Must be numeric value');

        if ($this->template->settings['members:groups:enabled']) {
            $groups = $this->database->table("categories")->where(
                "parent_id", $this->template->settings['members:group:categoryId']
            )->fetchPairs("id", "title");
            $groups[0] = 'Pro všechny';
            ksort($groups);

            $form->addSelect("group", "Skupina", $groups)
                ->setAttribute("class", "form-control");
        }

        $form->addText('weight', 'dictionary.main.Weight')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-primary");

        $arr = array(
            "prodid" => $this->getParameter("id"),
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = $this->editStockFormSucceeded;
        return $form;
    }

    public function editStockFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("stock")->get($form->values->id)
            ->update(array(
                "title" => $form->values->title,
                "amount" => $form->values->amount,
                "weight" => $form->values->weight,
                "partnr" => $form->values->partnr,
                "categories_id" => $form->values->group,
            ));

        $this->redirect(":AdminStore:Product:stock", array("id" => $form->values->prodid));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertBrandForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText('title', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertBrandFormSucceeded;
        return $form;
    }

    public function insertBrandFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")->insert(array(
            "categories_id" => $this->template->settings['categories:id:contactsBrands'],
            "title" => $form->values->title,
        ));

        $this->redirect(":AdminStore:Catalogue:brands", array("id" => null));
    }

    /**
     * Image Upload
     */
    function createComponentUploadForm()
    {
        $form = $this->baseFormFactory->createUI();
        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $store = $this->database->table("store")->get($this->getParameter("id"));

        $form->addHidden("id");
        $form->addHidden("pages_id");
        $form->addUpload('the_file', 'Vložit obrázek:')
            ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Invalid type of image:', $imageTypes);
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "pages_id" => $store->pages->id,
        ));

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $album = $form->values->pages_id;
        $fileDirectory = APP_DIR . '/media/' . $album . '/';

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $imageExists = $this->database->table("media")->where(array(
                'name' => $_FILES["the_file"]["name"],
                'pages_id' => $form->values->pages_id,
            ));

            $fileName = $fileDirectory . $_FILES["the_file"]["name"];
            \App\Model\IO::directoryMake(APP_DIR . '/media/' . $album . '/tn');
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);

            if ($imageExists->count() == 0) {
                $this->database->table("media")->insert(array(
                    'name' => $_FILES["the_file"]["name"],
                    'pages_id' => $form->values->pages_id,
                    'description' => $form->values->description,
                    'filesize' => filesize($fileDirectory . $_FILES["the_file"]["name"]),
                    'file_type' => 1,
                    'date_created' => date("Y-m-d H:i:s"),
                ));
            }

            // thumbnails
            $image = \Nette\Utils\Image::fromFile($fileName);
            $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/media/' . $album . '/tn/' . $_FILES["the_file"]["name"]);
            chmod(APP_DIR . '/media/' . $album . '/tn/' . $_FILES["the_file"]["name"], 0644);
        }

        $this->redirect(":AdminStore:Product:images", array(
            "id" => $form->values->id,
            "category" => $form->values->category,
        ));
    }

    /**
     * Search related
     */
    protected function createComponentSearchRelatedForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('src', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->searchRelatedFormSucceeded;
        return $form;
    }

    public function searchRelatedFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->redirect(":AdminStore:Product:related", array(
            "id" => $form->values->id,
            "src" => $form->values->src,
        ));
    }

    function handleDeleteStock($id)
    {
        $this->database->table("stock")->get($id)->delete();
        $this->redirect(":AdminStore:Product:stock", array("id" => $this->getParameter("product")));
    }

    function handleDeleteRelated($id)
    {
        $this->database->table("pages_related")->get($id)->delete();
        $this->redirect(":AdminStore:Product:related", array("id" => $this->getParameter("product")));
    }

    function handleInsertRelated($id)
    {
        $this->database->table("pages_related")->insert(array(
            "pages_id" => $this->getParameter("store"),
            "related_pages_id" => $id,
        ));
        $this->redirect(":AdminStore:Product:related", array("id" => $this->getParameter("store")));
    }

    /**
     * Delete file
     */
    function handleDeleteFile($id)
    {
        $image = $this->database->table("media")->get($id);
        $pageId = $image->pages_id;
        $this->database->table("media")->get($id)->delete();

        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $image->name);

        $this->redirect(":AdminStore:Product:files", array("id" => $pageId,));
    }

    public function renderDefault()
    {
        $this->template->stock = $this->database->table("stock")
            ->where(array("pages_id" => $this->getParameter("id")));


        $this->template->menu = $this->database->table("pages")->where('pages_types_id = 7 AND pages_id IS NULL');
        $categoriesSelected = $this->database->table("store_category")->where(array(
            'pages_id' => $this->getParameter("id"),
        ));

        foreach ($categoriesSelected as $arr) {
            $categories[] = $arr->category_pages_id;
        }

        $this->template->categories = $categories;
    }

    public function renderParams()
    {
        $this->template->params = $this->database->table("params")
            ->where(array("pages_id" => $this->getParameter("id")));
    }

    public function renderStock()
    {
        $this->template->stock = $this->database->table("stock")
            ->where(array("pages_id" => $this->getParameter("id")));
    }

    public function renderImagesDetail()
    {
        $this->template->images = $this->database->table("media")
            ->where(array(
                "pages_id" => $this->getParameter("name"),
                "id" => $this->getParameter("id"),
            ));
    }

    public function renderFiles()
    {
        $this->template->files = $this->database->table("media")
            ->where(array("pages_id" => $this->template->page->id, "file_type" => 0));
    }

    public function renderRelated()
    {
        $src = $this->getParameter("src");

        $this->template->relatedSearch = $this->database->table("pages")
            ->where(array(
                "title LIKE ?" => '%' . $src . '%',
                "pages_types_id" => 4,
            ))->limit(20);
        $this->template->related = $this->database->table("pages_related")
            ->where(array("pages_id" => $this->template->page->id));
        $this->template->relatedPairs = $this->template->related->fetchPairs('id', 'related_pages_id');
    }

}