<?php

namespace Caloriscz\Pricelist;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

class PricelistListEditControl extends Control
{

    /** @var Context */
    public $database;

    /**
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Menu Insert
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('pricelist');
        $form->addText('title', 'dictionary.main.Title')
            ->setRequired(true)
            ->addRule(Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addTextArea('description', 'Popisek')
            ->setAttribute('class', 'form-control');

        $pricelist = $this->database->table('pricelist_lists')->get($this->presenter->getParameter('pricelist'));

        if ($pricelist) {
            $form->setDefaults([
                'pricelist' => $pricelist->id,
                'title' => $pricelist->title,
                'description' => $pricelist->description
            ]);
        }

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {

        $this->database->table('pricelist_lists')->get($form->values->pricelist)->update([
            'title' => $form->values->title,
            'description' => $form->values->description,
        ]);

        $this->presenter->redirect(':Admin:Pricelist:menu', ['pricelist' => $form->values->pricelist]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $categoryId = null;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/PricelistListEditControl.latte');

        if ($this->presenter->getParameter('id')) {
            $categoryId = $this->presenter->getParameter('id');
        }

        $arr['parent_id'] = $categoryId;
        $arr['pricelist_lists_id'] = $this->presenter->getParameter('pricelist');

        $template->menuList = $this->database->table('pricelist_lists')->get($this->presenter->getParameter('pricelist'));
        $template->render();
    }

}
