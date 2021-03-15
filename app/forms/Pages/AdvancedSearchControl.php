<?php
namespace App\Forms\Pages;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Strings;

class AdvancedSearchControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearchForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator->domain('dictionary.main'));
        $form->setMethod('GET');
        $form->addHidden('idr', 'ID:');
        $form->addText('src')
            ->setHtmlAttribute('placeholder', Strings::firstUpper('src'));
        $form->addText('priceFrom');
        $form->addText('priceTo');
        $form->addText('brand');

        if ($this->getParameter('id')) {
            $form->setDefaults([
                'idr' => $this->presenter->getParameter('id'),
            ]);
        }

        $form->addSubmit('submitm', 'dictionary.main.Search')
            ->setHtmlAttribute('class', 'btn btn-info btn-lg');

        $form->onSuccess[] = [$this, 'searchFormSucceeded'];
        return $form;
    }

    public function searchFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getValues(true);

        unset($values['do'], $values['action'], $values['idr']);
        $values['id'] = $form->values->idr;

        $this->presenter->redirect('this', $values);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/AdvancedSearchControl.latte');
        $this->template->render();
    }

}