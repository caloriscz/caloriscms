<?php

namespace Caloriscz\Pricelist;

use Nette\Application\AbortException;
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
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('list');
        $form->addText('title', 'dictionary.main.Title')
            ->setRequired(true)
            ->addRule(Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addText('price', 'Cena')
            ->setRequired(true)
            ->addRule(Form::INTEGER, 'Zadávejte pouze čísla')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'width: 120px; text-align: right;');

        $categories = $this->database->table('pricelist_categories')
            ->where(['pricelist_lists_id' => $this->presenter->getParameter('pricelist')])
            ->order('id')->fetchPairs('id', 'title');

        $form->addSelect('category', 'dictionary.main.Categories')->setItems($categories)->setAttribute('class', 'form-control');;

        $pricelist = 1;

        if ($this->getParameter('pricelist')) {
            $pricelist = $this->presenter->getParameter('pricelist');
        }

        $form->setDefaults([
            'list' => $pricelist
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function insertFormSucceeded(Form $form, $values): void
    {
        $max = $this->database->table('pricelist')->max('sorted') + 1;

        $id = $this->database->table('pricelist')->insert([
            'title' => $values['title'],
            'pricelist_categories_id' => $form->getHttpData($form::DATA_TEXT, 'category'), // category in select empty, FFS
            'price' => $values['price'],
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
