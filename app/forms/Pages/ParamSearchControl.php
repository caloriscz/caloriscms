<?php
namespace App\Forms\Pages;

use Caloriscz\Page\PageSlugControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class ParamSearchControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentPageSlug()
    {
        return new PageSlugControl($this->database);
    }

    protected function createComponentSearchForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->setMethod('GET');

        $form->addHidden('idr');
        $form->addHidden('src');
        $form->addHidden('priceFrom');
        $form->addHidden('priceTo');
        $form->addHidden('brand');
        $form->addHidden('category');

        $form->setDefaults([
            'idr' => $this->presenter->getParameter('id'),
            'src' => $this->presenter->getParameter('src'),
            'priceFrom' => $this->presenter->getParameter('priceFrom'),
            'priceTo' => $this->presenter->getParameter('priceTo'),
            'brand' => $this->presenter->getParameter('brand'),
            'category' => $this->presenter->getParameter('page_id'),
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-info btn-lg');


        $form->onSuccess[] = [$this, 'searchFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function searchFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input
        unset($values['do'], $values['action'], $values['idr'], $values['locale']
            , $values['submitm'], $values['minsNow'], $values['maxNow'], $values['maxANow'], $values['category']);

        foreach ($values as $pmKey => $pmValue) {
            $pmKeyArr = explode("_", $pmKey);

            if (0 === strpos($pmKey, 'pm_')) {
                if ($pmKeyArr[1] == $pmKeyLast) {
                    $pmValues = $pmValues . "*" . $pmValue;
                } else {
                    $pmValues = $pmValue;
                }

                $pmKeyLast = $pmKeyArr[1];

                if (isset($pmKeyArr[3])) {
                    $pmType = "_" . $pmKeyArr[3];
                } else {
                    $pmType = null;
                }

                $pmArray["pm_" . $pmKeyArr[1] . $pmType] = $pmValues;
            }
        }

        /* Unset params pm_ from the $values */
        foreach ($values as $key => $value) {
            if (strpos($key, 'pm_') !== false) {
                unset($values[$key]);
            }
        }

        $category = $form->values->category;
        $catalogue = $this->database->table('pages')->get($category);

        $this->presenter->redirectUrl('/' . $catalogue->slug . '?' . http_build_query(array_merge(array_filter($values), array_filter($pmArray))));
    }

    protected function createComponentPageTitle()
    {
        return new \Caloriscz\Page\PageTitleControl($this->database);
    }

    public function render()
    {
        $template = $this->template;
        $template->database = $this->database;
        $template->selected = $this->getParams();

        $template->qs = array_filter($this->presenter->getParameters());
        unset($template->qs['action'], $template->qs['page_id']);

        if ($this->presenter->translator->getLocale() !== $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
        }

        if ($this->presenter->getParameter('page_id') === 20) {
            $params = $this->database->table('param')->where([
                'ignore_front' => 0,
            ]);

        } else {
            $params = $this->database->table('param')->where([
                ':param_categories.pages_id' => $this->presenter->getParameter('page_id'),
                'ignore_front' => 0,
            ])->order('sorted');
        }

        $template->params = $params;
        $template->page = $this->presenter->template->page;

        $template->setFile(__DIR__ . '/ParamSearchControl.latte');

        $template->render();
    }

    /**
     * Function for getting parrams into array
     */
    function getParams()
    {
        $values = $this->presenter->getParameters(true);

        foreach ($values as $pmKey => $pmValue) {
            $pmKeyArr = explode('_', $pmKey);

            if (0 === strpos($pmKey, 'pm_')) {
                if ($pmKeyArr[1] === $pmKeyLast) {
                    $pmValues = $pmValues . '*' . $pmValue;
                } else {
                    $pmValues = $pmValue;
                }

                $pmKeyLast = $pmKeyArr[1];

                if (isset($pmKeyArr[3])) {
                    $pmType = '_' . $pmKeyArr[3];
                } else {
                    $pmType = null;
                }

                $pmArray['pm_' . $pmKeyArr[1] . $pmType] = urldecode($pmValues);
            }
        }

        return $pmArray;
    }
}
