<?php
namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditContactFormControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit helpdesk
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('helpdesk_id');
        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('style', 'height: 200px;');
        $form->addCheckbox('fill_phone');

        $helpdesk = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));

        $form->setDefaults([
            'helpdesk_id' => $helpdesk->id,
            'title' => $helpdesk->title,
            'description' => $helpdesk->description,
            'fill_phone' => $helpdesk->fill_phone,
        ]);

        $form->addSubmit('submitm', 'Uložit');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('helpdesk')->get($form->values->helpdesk_id)->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
                'fill_phone' => $form->values->fill_phone,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->helpdesk_id]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditContactFormControl.latte');
        $template->render();
    }

}