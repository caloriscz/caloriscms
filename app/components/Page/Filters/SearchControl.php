<?php

namespace Caloriscz\Page\Filters;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class SearchControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Filter
     */
    protected function createComponentSearchTopForm()
    {
        $form = new BootstrapUIForm();

        $form->setMethod('GET');
        $form->getElementPrototype()->class = 'form-inline';

        $form->addHidden('idr');
        $form->addText('src')
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Hledat');
        $form->addText('priceFrom')
            ->setAttribute('style', 'width: 50px;');
        $form->addText('priceTo')
            ->setAttribute('style', 'width: 50px;');
        $form->addText('brand');

        if ($this->getParameter('id')) {
            $form->setDefaults(['idr' => $this->getParameter('id')]);
        }

        $form->addSubmit('submitm', 'dictionary.main.Search')
            ->setAttribute('class', 'btn btn-info btn-lg')
            ->setAttribute('placeholder', 'dictionary.main.Search');


        $form->onSuccess[] = [$this, 'searchTopFormSucceeded'];
        return $form;
    }

    public function searchTopFormSucceeded(BootstrapUIForm $form)
    {
        $values = $form->getValues(true);
        unset($values['do'], $values['action'], $values['idr']);

        $this->presenter->redirect(':Front:Catalogue:default', $values);
    }

    public function render()
    {
        $this->template->isLoggedIn = $this->presenter->template->isLoggedIn;
        $this->template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/SearchControl.latte');
        $this->template->render();
    }

}
