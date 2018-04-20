<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Pricelist presenter.
 */
class PricelistPresenter extends BasePresenter
{

    protected function createComponentPricelistCategory()
    {
        $control = new \Caloriscz\Pricelist\CategoryControl($this->database);
        return $control;
    }

    protected function createComponentPricelistNewItem()
    {
        $control = new \Caloriscz\Pricelist\NewItemControl($this->database);
        return $control;
    }

    protected function createComponentPricelistEditItem()
    {
        $control = new \Caloriscz\Pricelist\EditItemControl($this->database);
        return $control;
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

        $form = $this->baseFormFactory->createUI();

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
        $category = $this->database->table("pricelist_categories")->order("id")->fetchPairs('id', 'title');
        $form = $this->baseFormFactory->createUI();

        $form->addHidden('day');
        $form->addTextarea('title', 'dictionary.main.Title')
            ->setHtmlId("wysiwyg-sm")
            ->setAttribute("class", "form-control")
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'Kategorie', $category)
            ->setAttribute("class", "form-control");
        $form->addText('price', 'dictionary.main.Price')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute("style", "width: 50px; text-align: right;");

        $form->setDefaults(array(
            "day" => $this->getParameter("day"),
        ));

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertDailyFormSucceeded;
        return $form;
    }

    public function insertDailyFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("pricelist_daily")->insert(array(
            "title" => $form->values->title,
            "pricelist_categories_id" => $form->values->category,
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
            "pricelist_categories_id" => $category,
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
            "pricelist_categories_id" => $category,
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
        $this->template->database = $this->database;

        $this->template->pricelist = $this->database->table("pricelist")
            ->select("pricelist.id, pricelist.pricelist_categories_id, pricelist.title AS amenu, pricelist.sorted, pricelist.price, pricelist_categories.title")
            ->order("pricelist_categories_id, sorted DESC");
    }

    public function renderDays()
    {
        $this->template->days = $this->database->table("pricelist_dates")->order("day");
    }

    public function renderDaily()
    {

        $this->template->menu = $this->database->table("pricelist_daily")
            ->select("pricelist_daily.id, pricelist_daily.pricelist_dates_id, pricelist_daily.categories_id, "
                . "pricelist_daily.title AS amenu, pricelist_daily.price, pricelist_categories.title")
            ->where(array("pricelist_daily.pricelist_dates_id" => $this->getParameter("day")))
            ->order("categories_id");
    }

    function handleGeneratePdf($id)
    {
        $file = substr(APP_DIR, 0, -4) . '/app/AdminModule/templates';

        $pricelist = $this->database->table("pricelist")
            ->select("pricelist.id, pricelist.categories_id, pricelist.title AS amenu, pricelist.sorted, pricelist.price, categories.title")
            ->order("categories_id, sorted DESC");


        $params = array(
            'pricelist' => $pricelist,
            'settings' => $this->template->settings,
        );

        $latte = new \Latte\Engine;
        $template = $latte->renderToString($file . "/components/pricelist.latte", $params);
        $pdf = new \Joseki\Application\Responses\PdfResponse($template);
        $pdf->setSaveMode(\Joseki\Application\Responses\PdfResponse::INLINE);
        //$pdf->save(APP_DIR . '/files/invoices-125/', 'ivt-' . $order->oid . '.pdf');
        //echo APP_DIR . '/files/invoices-125' . '/' .  'ivt-' . $order->oid . '.pdf';
        //$pdf->setSaveMode(\Joseki\Application\Responses\PdfResponse::DOWNLOAD); //default behavior
        $this->sendResponse($pdf);
    }

}
