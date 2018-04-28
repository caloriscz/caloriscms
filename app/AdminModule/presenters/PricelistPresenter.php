<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Pricelist\CategoryControl;
use Caloriscz\Pricelist\EditItemControl;
use Caloriscz\Pricelist\NewItemControl;
use Joseki\Application\Responses\PdfResponse;
use Latte\Engine;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

/**
 * Pricelist presenter.
 */
class PricelistPresenter extends BasePresenter
{

    protected function createComponentPricelistCategory()
    {
        return new CategoryControl($this->database);
    }

    protected function createComponentPricelistNewItem()
    {
        return new NewItemControl($this->database);
    }

    protected function createComponentPricelistEditItem()
    {
        return new EditItemControl($this->database);
    }

    /**
     * Menu Insert Day
     */
    protected function createComponentInsertDayForm()
    {
        for ($d = 0; $d < 30; $d++) {
            $dateExists = $this->database->table('pricelist_dates')->where(array(
                'day' => date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $d, date('Y')))));

            if ($dateExists->count() === 0) {
                $dates[date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $d, date('Y')))] = date('j.n. Y', mktime(0, 0, 0, date('m'), date('d') + $d, date('Y')));
            }
        }

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addSelect('day', 'Den', $dates)
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'width: 120px;');
        $form->addSubmit('submitm', 'Přidat');

        $form->onSuccess[] = $this->insertDayFormSucceeded;
        return $form;
    }

    public function insertDayFormSucceeded(BootstrapUIForm $form)
    {
        $id = $this->database->table('pricelist_dates')->insert([
            'day' => $form->values->day,
        ]);

        $this->redirect(':Admin:Pricelist:daily', ['day' => $id->id]);
    }

    /**
     * Menu Insert
     */
    protected function createComponentInsertDailyForm()
    {
        $category = $this->database->table('pricelist_categories')->order('id')->fetchPairs('id', 'title');
        $form = $this->baseFormFactory->createUI();

        $form->addHidden('day');
        $form->addTextarea('title', 'dictionary.main.Title')
            ->setHtmlId('wysiwyg-sm')
            ->setAttribute('class', 'form-control')
            ->addRule(Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'Kategorie', $category)
            ->setAttribute('class', 'form-control');
        $form->addText('price', 'dictionary.main.Price')
            ->addRule(Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute('style', 'width: 50px; text-align: right;');

        $form->setDefaults(array(
            'day' => $this->getParameter('day'),
        ));

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertDailyFormSucceeded;
        return $form;
    }

    public function insertDailyFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table('pricelist_daily')->insert(array(
            'title' => $form->values->title,
            'pricelist_categories_id' => $form->values->category,
            'price' => $form->values->price,
            'pricelist_dates_id' => $form->values->day,
        ));

        $this->redirect(':Admin:Pricelist:daily', ['day' => $form->values->day]);
    }

    /**
     * Delete food
     */
    public function handleDelete($id)
    {
        $this->database->table('pricelist')->get($id)->delete();

        $this->redirect(':Admin:Pricelist:default', ['id' => null]);
    }

    /**
     * Delete daily food
     */
    public function handleDeleteDaily($id, $day)
    {
        $this->database->table('pricelist_daily')->get($id)->delete();

        $this->redirect(':Admin:Pricelist:daily', ['day' => $day]);
    }

    /**
     * Delete daily food
     */
    public function handleDeleteDay($id)
    {
        $this->database->table('pricelist_dates')->where(['id' => $id])->delete();

        $this->redirect(':Admin:Pricelist:days');
    }

    public function handleUp($id, $sorted, $category)
    {
        $sortDb = $this->database->table('pricelist')->where([
            'sorted > ?' => $sorted,
            'pricelist_categories_id' => $category,
        ])->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('pricelist')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('pricelist')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Pricelist:default', ['id' => null]);
    }

    public function handleDown($id, $sorted, $category)
    {
        $sortDb = $this->database->table('pricelist')->where([
            'sorted < ?' => $sorted,
            'pricelist_categories_id' => $category,
        ])->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('pricelist')->where(['id' => $id])->update(['sorted' => $sort->sorted]);
            $this->database->table('pricelist')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect(':Admin:Pricelist:default', ['id' => null]);
    }

    public function renderDefault()
    {
        $this->template->database = $this->database;

        $this->template->pricelist = $this->database->table('pricelist')
            ->select('pricelist.id, pricelist.pricelist_categories_id, pricelist.title AS amenu, pricelist.sorted, pricelist.price, pricelist_categories.title')
            ->order('pricelist_categories_id, sorted DESC');
    }

    public function renderDays()
    {
        $this->template->days = $this->database->table('pricelist_dates')->order('day');
    }

    public function renderDaily()
    {

        $this->template->menu = $this->database->table('pricelist_daily')
            ->select('pricelist_daily.id, pricelist_daily.pricelist_dates_id, pricelist_daily.categories_id, '
                . 'pricelist_daily.title AS amenu, pricelist_daily.price, pricelist_categories.title')
            ->where(['pricelist_daily.pricelist_dates_id' => $this->getParameter('day')])
            ->order('categories_id');
    }
}
