<?php

namespace App\Forms\Pages;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class SearchControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Filter
     */
    protected function createComponentSearchTopForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setMethod('GET');
        $form->getElementPrototype()->class = 'form-inline';

        $form->addHidden('idr');
        $form->addText('src')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', 'Hledat');
        $form->addText('priceFrom')
            ->setHtmlAttribute('style', 'width: 50px;');
        $form->addText('priceTo')
            ->setHtmlAttribute('style', 'width: 50px;');
        $form->addText('brand');

        if ($this->getParameter('id')) {
            $form->setDefaults(['idr' => $this->getParameter('id')]);
        }

        $form->addSubmit('submitm', 'Hledat')
            ->setHtmlAttribute('class', 'btn btn-info btn-lg')
            ->setHtmlAttribute('placeholder', 'Hledat');


        $form->onSuccess[] = [$this, 'searchTopFormSucceeded'];
        return $form;
    }

    public function searchTopFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getValues(true);
        unset($values['do'], $values['action'], $values['idr']);

        $this->presenter->redirect('this', $values);
    }

    public function render(): void
    {
        $this->template->isLoggedIn = $this->presenter->template->isLoggedIn;
        $this->template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/SearchControl.latte');
        $this->template->render();
    }

}
