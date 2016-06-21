<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Pricelist presenter.
 */
class PricelistPresenter extends BasePresenter
{

    /**
     * Menu Insert
     */
    protected function createComponentInsertForm()
    {
        $category = $this->database->table("categories")
            ->where("parent_id", $this->template->settings['categories:id:pricelist'])
            ->order("id")->fetchPairs('id', 'title');
        $form = $this->baseFormFactory->createUI();

        $form->addText('title', 'Název')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'dictionary.main.Categories', $category)
            ->setAttribute("class", "form-control");
        $form->addText('price', 'Cena')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávejte pouze čísla')
            ->setAttribute("style", "width: 80px; text-align: right;");
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    public function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $max = $this->database->table("pricelist")->max("sorted") + 1;

        $id = $this->database->table("pricelist")->insert(array(
            "title" => $form->values->title,
            "categories_id" => $form->values->category,
            "price" => $form->values->price,
            "sorted" => $max,
        ));

        $this->redirect(":Admin:Pricelist:menuedit", array("id" => $id));
    }

    /**
     * Menu Insert
     */
    protected function createComponentEditForm()
    {
        $item = $this->database->table("pricelist")->get($this->getParameter("id"));
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('title', 'Výkon')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávajte delší text', 1);
        $form->addTextArea('description', 'Popis')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávajte delší text', 1)
            ->setHtmlId("wysiwyg-sm");
        $form->addText('price', 'Cena')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute("style", "width: 80px; text-align: right;");
        $form->addText('price_info'
            . '', 'Info za cenou');


        $form->setDefaults(array(
            "title" => $item->title,
            "description" => $item->description,
            "price" => $item->price,
            "price_info" => $item->price_info,
            "id" => $item->id,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("pricelist")->where(array(
            "id" => $form->values->id,
        ))->update(array(
            "title" => $form->values->title,
            "description" => $form->values->description,
            "price" => $form->values->price,
            "price_info" => $form->values->price_info,
        ));

        $this->redirect(":Admin:Pricelist:menuedit", array("id" => $form->values->id));
    }

    /**
     * Menu Insert Day
     */
    protected function createComponentInsertDayForm()
    {
        for ($d = 0; $d < 30; $d++) {
            $dateExists = $this->database->table("pricelist_dates")->where(array(
                "day" => date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $d, date("Y")))));

            if ($dateExists->count() == 0) {
                $dates[date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $d, date("Y")))] = date("j.n. Y", mktime(0, 0, 0, date("m"), date("d") + $d, date("Y")));
            }
        }

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addSelect('day', 'Den', $dates)
            ->setAttribute("class", "form-control")
            ->setAttribute("style", "width: 120px;");
        $form->addSubmit('submitm', 'Přidat');

        $form->onSuccess[] = $this->insertDayFormSucceeded;
        return $form;
    }

    public function insertDayFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table("pricelist_dates")->insert(array(
            "day" => $form->values->day,
        ));

        $this->redirect(":Admin:Pricelist:daily", array("day" => $id->id));
    }

    /**
     * Menu Insert
     */
    protected function createComponentInsertDailyForm()
    {
        $category = $this->database->table("categories")->order("id")->fetchPairs('id', 'title');
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('day');
        $form->addTextarea('title', 'dictionary.main.Title')
            ->setHtmlId("wysiwyg-sm")
            ->setAttribute("class", "form-control")
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'Kategorie', $category)
            ->setAttribute("class", "form-control");
        $form->addText('price', 'Cena')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute("style", "width: 50px; text-align: right;");

        $form->setDefaults(array(
            "day" => $this->getParameter("day"),
        ));

        $form->addSubmit('submitm', 'Přidat');

        $form->onSuccess[] = $this->insertDailyFormSucceeded;
        return $form;
    }

    public function insertDailyFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("pricelist_daily")->insert(array(
            "title" => $form->values->title,
            "categories_id" => $form->values->category,
            "price" => $form->values->price,
            "pricelist_dates_id" => $form->values->day,
        ));

        $this->redirect(":Admin:Pricelist:daily", array("day" => $form->values->day));
    }

    /**
     * Delete food
     */
    function handleDelete($id)
    {
        $this->database->table("pricelist")->get($id)->delete();

        $this->redirect(":Admin:Pricelist:default", array("id" => null));
    }

    /**
     * Delete daily food
     */
    function handleDeleteDaily($id, $day)
    {
        $this->database->table("pricelist_daily")->get($id)->delete();

        $this->redirect(":Admin:Pricelist:daily", array("day" => $day));
    }

    /**
     * Delete daily food
     */
    function handleDeleteDay($id)
    {
        $this->database->table("pricelist_dates")->where(array("id" => $id))->delete();

        $this->redirect(":Admin:Pricelist:days");
    }

    function handleUp($id, $sorted, $category)
    {
        $sortDb = $this->database->table("pricelist")->where(array(
            "sorted > ?" => $sorted,
            "categories_id" => $category,
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("pricelist")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("pricelist")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Pricelist:default", array("id" => null));
    }

    function handleDown($id, $sorted, $category)
    {
        $sortDb = $this->database->table("pricelist")->where(array(
            "sorted < ?" => $sorted,
            "categories_id" => $category,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("pricelist")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("pricelist")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Pricelist:default", array("id" => null));
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:pricelist'];

        $this->template->database = $this->database;

        $this->template->pricelist = $this->database->table("pricelist")
            ->select("pricelist.id, pricelist.categories_id, pricelist.title AS amenu, pricelist.sorted, pricelist.price, categories.title")
            ->order("categories_id, sorted DESC");
    }

    public function renderDays()
    {
        $this->template->days = $this->database->table("pricelist_dates")->order("day");
    }

    public function renderDaily()
    {

        $this->template->menu = $this->database->table("pricelist_daily")
            ->select("pricelist_daily.id, pricelist_daily.pricelist_dates_id, pricelist_daily.categories_id, "
                . "pricelist_daily.title AS amenu, pricelist_daily.price, categories.title")
            ->where(array("pricelist_daily.pricelist_dates_id" => $this->getParameter("day")))
            ->order("categories_id");
    }

}
