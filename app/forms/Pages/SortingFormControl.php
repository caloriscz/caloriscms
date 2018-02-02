<?php
namespace App\Forms\Pages;

use App\Model\Page;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class SortingFormControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Sorting form
     */
    protected function createComponentSortingForm()
    {
        $sortCols = array(
            'dd' => 'dictionary.sorting.new',
            'da' => 'dictionary.sorting.old',
            'pa' => 'dictionary.sorting.cheap',
            'pd' => 'dictionary.sorting.expensive',
            'na' => 'dictionary.sorting.az',
            'nd' => 'dictionary.sorting.za',
        );

        if ($this->presenter->getParameter("o") === '') {
            $sort = 'dd';
        } else {
            $sort = $this->presenter->getParameter("o");
        }

        $form = new BootstrapUIForm();
        $form->setMethod('GET');
        $form->getElementPrototype()->class = 'form-horizontal form-sorting';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = 'simpleSort';
        $form->getElementPrototype()->id = 'order-me';
        $form->getElementPrototype()->onchange = 'document.getElementById(\'order-me\').submit(); ';
        $form->setTranslator($this->presenter->translator);
        $form->addHidden('brand');
        $form->addHidden('category');
        $form->addHidden('id');
        $form->addHidden('page');
        $form->addHidden('priceFrom');
        $form->addHidden('priceTo');
        $form->addHidden('src');
        $form->addHidden('user');
        $form->addSelect('o', 'dictionary.main.Sort', $sortCols)
            ->setAttribute('class', 'sortsel');
        $form->addSubmit('sort', 'Seřadit')
            ->setAttribute('class', 'btn btn-primary btn-xs sortBtn')
            ->setAttribute('style', 'display: none; height: 31px;');

        $form->setDefaults([
            'src' => $this->presenter->getParameter('src'),
            'brand' => $this->presenter->getParameter('brand'),
            'category' => $this->presenter->getParameter('page_id'),
            'o' => $this->presenter->translator->translate($sort),
            'priceFrom' => $this->presenter->getParameter('priceFrom'),
            'priceTo' => $this->presenter->getParameter('priceTo'),
            'page' => $this->presenter->getParameter('page'),
            'user' => $this->presenter->getParameter('user'),
        ]);

        $form->onSuccess[] = [$this, 'sortingFormSucceeded'];
        return $form;
    }

    public function sortingFormSucceeded(BootstrapUIForm $form)
    {
        $filter = array_filter($form->getValues(TRUE));
        unset($filter['do'], $filter['action'], $filter['category']);

        $pageDb = new Page($this->database);
        $page = $pageDb->getPageById($form->values->category);

        $this->presenter->redirectUrl('/' . $page->slug . '?' . http_build_query($filter));
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/SortingFormControl.latte');

        $template->render();
    }

}