<?php

namespace Caloriscz\Pricelist;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class CategoryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert category
     */
    protected function createComponentInsertCategoryForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('parent_id');
        $form->addHidden('type');
        $form->addText('title', 'dictionary.main.title')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'dictionary.main.insert')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'insertCategoryFormSucceeded'];
        $form->onValidate[] = [$this, 'validateCategoryFormSucceeded'];
        return $form;
    }

    public function validateCategoryFormSucceeded(BootstrapUIForm $form)
    {
        $redirectTo = $this->presenter->getName();

        $category = $this->database->table('categories')->where([
            'parent_id' => $form->values->parent_id,
            'title' => $form->values->title,
        ]);

        if ($category->count() > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.categoryAlreadyExists'), 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }

        if ($form->values->title == '') {
            $this->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }
    }

    public function insertCategoryFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('pricelist_categories')->insert([
            'title' => $form->values->title,
            'parent_id' => $form->values->parent_id,
        ]);

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/CategoryControl.latte');

        $template->idActive = $this->presenter->getParameter('id');
        $template->menu = $this->database->table('pricelist_categories');
        $template->render();
    }

}
