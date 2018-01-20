<?php
namespace Caloriscz\Page\Filters;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Strings;

class AdvancedSearchControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearchForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator->domain('dictionary.main'));
        $form->setMethod('GET');
        $form->getElementPrototype()->class = 'form-inline';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('idr', 'ID:');
        $form->addText('src')
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', Strings::firstUpper('src'));
        $form->addText('priceFrom')
            ->setAttribute('style', 'width: 50px;');
        $form->addText('priceTo')
            ->setAttribute('style', 'width: 50px;');
        $form->addText('brand');

        if ($this->getParameter('id')) {
            $form->setDefaults(array(
                'idr' => $this->presenter->getParameter('id'),
            ));
        }

        $form->addSubmit('submitm', 'dictionary.main.Search')
            ->setAttribute('class', 'btn btn-info btn-lg');


        $form->onSuccess[] = [$this, 'searchFormSucceeded'];
        return $form;
    }

    public function searchFormSucceeded(BootstrapUIForm $form)
    {
        $values = $form->getValues(TRUE);

        unset($values['do'], $values['action'], $values['idr']);
        $values['id'] = $form->values->idr;

        $this->presenter->redirect(':Front:Catalogue:default', $values);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/AdvancedSearchControl.latte');
        $this->template->render();
    }

}