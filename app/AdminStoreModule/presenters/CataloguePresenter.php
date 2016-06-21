<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Catalogue presenter.
 */
class CataloguePresenter extends BasePresenter
{

    /**
     * Insert product
     */
    protected function createComponentInsertProductForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText('title', 'dictionary.main.ProductTitle');
        $form->addSubmit('submitm', 'dictionary.main.Save')->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertProductFormSucceeded;
        return $form;
    }

    public function insertProductFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new Model\Document($this->database);
        $doc->setType(4);
        $doc->setTitle($form->values->title);
        

        $page = $doc->create($this->user->getId());

        $id = $this->database->table("store")->insert(array(
            "pages_id" => $page,
        ));

        Model\IO::directoryMake(APP_DIR . '/media/' . $page, 0755);

        $this->redirect(":AdminStore:Product:default", array("id" => $id));
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
            "title" => $form->values->title,
            "categories_id" => $this->template->settings['categories:id:contactsBrands'],
        ));

        $this->redirect(":AdminStore:Catalogue:brands", array("id" => null));
    }

    /**
     * Delete product - check if it does not exist in Orders and similar table, otherwise zero stock and hide product (show = 0)
     * @param type $id
     */
    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $ordersDb = $this->database->table("orders_items")->where("store_id", $id[$a]);


            if ($ordersDb->count() > 0) {
                $this->flashMessage("Nelze smazat. Pro tento produkt existují objednávky. Počet: " . $ordersDb->count(), "note");
            } else {
                $product = $this->database->table("store")->get($id[$a]);
                $this->database->table("doc")->get($product->doc_id)->delete();

                $slugName = new Model\Slug($this->database);
                $slugName->remove($product->slug_id);

                $product->delete();

                Model\IO::removeDirectory(APP_DIR . '/media/' . $id[$a]);
            }
        }

        $this->redirect(":AdminStore:Catalogue:default", array("id" => null));
    }

    /**
     * Sorting
     */
    function handleSort()
    {
        if ($this->getParameter('prev_id')) {
            $prev = $this->database->table("pages")->get($this->getParameter('prev_id'));

            $this->database->table("pages")->where(array("id" => $this->getParameter('item_id')))->update(array("sorted" => ($prev->sorted + 1)));
        } else {
            $next = $this->database->table("pages")->get($this->getParameter('next_id'));

            $this->database->table("pages")->where(array("id" => $this->getParameter('item_id')))->update(array("sorted" => ($next->sorted - 1)));
        }

        $this->database->query("SET @i = 1;UPDATE `pages` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC");

        $this->redirect(":AdminStore:Catalogue:default", array("id" => $this->getParameter('cat')));
    }

    /**
     * Sorting
     */
    function handleToggle($id)
    {
        $sorted1 = $this->database->table("store")->get($id)->sorted;
        $sorted2 = $this->database->table("store")->get($this->getParameter('toggled'))->sorted;


        $this->database->table("store")->where(array("id" => $id))->update(array("sorted" => $sorted2));
        $this->database->table("store")->where(array("id" => $this->getParameter('toggled')))->update(array("sorted" => $sorted1));

        $this->redirect(":AdminStore:Catalogue:default", array("id" => $this->getParameter('cat')));
    }

    public function createComponentSimpleGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->getParameter("id")) {
            $idCategory = $this->getParameter("id");
        } else {
            $idCategory = null;
        }

        $filter = new \App\Model\Store\Filter($this->database);
        $filter->order('oa');
        $filter->setCategories($idCategory);
        $filter->setManufacturer($this->getParameter("brand"));
        $filter->setUser($this->getParameter("user"));
        $filter->setText($this->getParameter("src"));
        $this->template->settings["store:stock:hideEmpty"] = 0; // unhide empty products for admin filter
        $filter->setOptions($this->template->settings);

        $test = $filter->assemble();

        $grid->setDataSource($test);

        //$grid->setItemsPerPageList(array(20));
        $grid->setSortable(true);

        $grid->addGroupAction('Delete examples')->onSelect[] = [$this, 'handleDelete'];

        $grid->addColumnText('test', 'dictionary.main.Image')
            ->setRenderer(function ($item) {
                $media = $this->database->table("media")->where("pages_id", $item->id);

                if ($media->count() > 0) {
                    $fileImage = \Nette\Utils\Html::el('img', array('style' => 'max-height: 30px;'))
                        ->src('/media/' . $item->id . '/tn/' . $media->fetch()->name);
                } else {
                    $fileImage = null;
                }

                return $fileImage;
            });
        $grid->addColumnText('title', 'dictionary.main.Image')
            ->setRenderer(function ($item) {
                return \Nette\Utils\Html::el('a')->href('/admin/store/product/default/' . $item->id)->setText($item->title);
            });
        $grid->addColumnText('price', 'dictionary.main.Price');
        $grid->addColumnText('sorted', 'sorted')->setSortable();

        $grid->setTranslator($this->translator);
    }

    public function renderDefault()
    {
        if ($this->getParameter("id")) {
            $category = new Model\Category($this->database);
            $this->template->breadcrumbs = $category->getBreadcrumb($this->getParameter("id"));
        }
    }
}
