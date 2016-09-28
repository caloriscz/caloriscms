<?php
namespace Caloriscz\Page\Related;

use Nette\Application\UI\Control;

class FilterFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentFilterForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('src', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Search');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->filterFormSucceeded;
        return $form;
    }

    public function filterFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->presenter->redirect(this, array(
            "id" => $form->values->id,
            "src" => $form->values->src,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FilterFormControl.latte');

        $template->render();
    }

}
