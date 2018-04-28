<?php

namespace Caloriscz\Pricelist;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

class NewItemControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Menu Insert
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $category = $this->database->table('pricelist_categories')->order('id')->fetchPairs('id', 'title');
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'dictionary.main.Title')
            ->setRequired(true)
            ->addRule(Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'dictionary.main.Categories', $category)
            ->setAttribute('class', 'form-control');
        $form->addText('price', 'Cena')
            ->setRequired(true)
            ->addRule(Form::INTEGER, 'Zadávejte pouze čísla')
            ->setAttribute('style', 'width: 80px; text-align: right;');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $max = $this->database->table('pricelist')->max('sorted') + 1;

        $id = $this->database->table('pricelist')->insert([
            'title' => $form->values->title,
            'pricelist_categories_id' => $form->values->category,
            'price' => $form->values->price,
            'sorted' => $max,
        ]);

        $this->presenter->redirect(':Admin:Pricelist:menuedit', ['id' => $id]);
    }


    public function render()
    {
        $template = $this->getTemplate();

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/NewItemControl.latte');

        $template->idActive = $this->presenter->getParameter("id");
        $template->menu = $this->database->table('pricelist_categories');
        $template->render();
    }

}
