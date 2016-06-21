<?php

class BaseForm extends Nette\Object
{

    /** @var \Kdyby\Translation\Translator @inject */
    private $translator;
    private $database;

    public function __construct(Nette\Database\Connection $database, \Kdyby\Translation\Translator $translator)
    {
        $this->database = $database;
        $this->translator = $translator;
    }

    public function createUI()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        return $form;
    }

    public function createPH()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        return $form;
    }

    public function createFF()
    {
        $form = new \Nette\Forms\FilterForm();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        return $form;
    }

}
