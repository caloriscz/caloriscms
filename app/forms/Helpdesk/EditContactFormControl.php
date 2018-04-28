<?php
namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditContactFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    /**
     * EditContactFormControl constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit helpdesk
     * @return BootstrapUIForm
     */
    public function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('helpdesk_id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'height: 200px;');
        $form->addCheckbox('fill_phone');

        $helpdesk = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));

        $form->setDefaults([
            'helpdesk_id' => $helpdesk->id,
            'title' => $helpdesk->title,
            'description' => $helpdesk->description,
            'fill_phone' => $helpdesk->fill_phone,
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('helpdesk')->get($form->values->helpdesk_id)
            ->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
                'fill_phone' => $form->values->fill_phone,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->helpdesk_id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditContactFormControl.latte');
        $template->render();
    }

}