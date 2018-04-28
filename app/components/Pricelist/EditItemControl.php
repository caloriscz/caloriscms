<?php

namespace Caloriscz\Pricelist;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;

/**
 * Menu Editor
 * @package Caloriscz\Pricelist
 */
class EditItemControl extends Control
{

    /** @var Context */
    public $database;

    /**
     * EditItemControl constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm()
    {
        $item = $this->database->table('pricelist')->get($this->presenter->getParameter('id'));
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addText('title', 'Výkon')
            ->setRequired(true)
            ->addRule(Form::MIN_LENGTH, 'Zadávajte delší text', 1);
        $form->addTextArea('description', 'Popis')
            ->setHtmlId('wysiwyg-sm');
        $form->addText('price', 'Cena')
            ->setRequired(true)
            ->addRule(Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'width: 120px; text-align: right;');
        $form->addText('price_info'
            . '', 'Info za cenou');


        $form->setDefaults([
            'title' => $item->title,
            'description' => $item->description,
            'price' => $item->price,
            'price_info' => $item->price_info,
            'id' => $item->id,
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('pricelist')->where([
            'id' => $form->values->id,
        ])->update([
            'title' => $form->values->title,
            'description' => $form->values->description,
            'price' => $form->values->price,
            'price_info' => $form->values->price_info,
        ]);

        $this->presenter->redirect(':Admin:Pricelist:menuedit', ['id' => $form->values->id]);
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditItemControl.latte');

        $template->render();
    }

}
