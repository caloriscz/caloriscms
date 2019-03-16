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

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentFilterForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('src', 'Název');
        $form->addSubmit('submitm', 'Hledání');

        $form->setDefaults([
            'id' => $this->getParameter('id')
        ]);

        $form->onSuccess[] = [$this, 'filterFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function filterFormSucceeded(BootstrapUIForm $form): void
    {
        $this->presenter->redirect('this', [
            'id' => $form->values->id,
            'src' => $form->values->src,
        ]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/FilterFormControl.latte');
        $template->render();
    }

}
