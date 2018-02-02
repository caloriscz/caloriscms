<?php
namespace App\Forms\Pages;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class FilterFormControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentFilterForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('src', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Search');

        $form->setDefaults([
            'id' => $this->getParameter('id')
        ]);

        $form->onSuccess[] = [$this, 'filterFormSucceeded'];
        return $form;
    }

    public function filterFormSucceeded(BootstrapUIForm $form)
    {
        $this->presenter->redirect('this', [
            'id' => $form->values->id,
            'src' => $form->values->src,
        ]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/FilterFormControl.latte');
        $template->render();
    }

}
