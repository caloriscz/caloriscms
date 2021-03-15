<?php
namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditMailTemplateControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * E-mail template edit
     * @return BootstrapUIForm
     */
    public function createComponentEditForm(): BootstrapUIForm
    {
        $emailDb = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->id = 'search-form';


        $form->addHidden('id');
        $form->addText('subject');
        $form->addTextArea('body')
            ->setHtmlAttribute('style', 'width: 500px;')
            ->setHtmlId('wysiwyg');

        $form->setDefaults([
            'id' => $this->presenter->getParameter('id'),
            'subject' => $emailDb->subject,
            'body' => $emailDb->body,
        ]);


        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('helpdesk')->get($form->values->id)->update([
                'subject' => $form->values->subject,
                'body' => $form->values->body,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->id]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditMailTemplateControl.latte');
        $template->render();
    }

}