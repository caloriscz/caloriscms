<?php
namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditMailTemplateControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * E-mail template edit
     */
    public function createComponentEditForm()
    {
        $emailDb = $this->database->table('helpdesk_emails')->get($this->presenter->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->id = 'search-form';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('subject');
        $form->addTextArea('body')
            ->setAttribute('style', 'width: 500px;')
            ->setHtmlId('wysiwyg');

        $form->setDefaults(array(
            'id' => $this->presenter->getParameter('id'),
            'subject' => $emailDb->subject,
            'body' => $emailDb->body,
        ));


        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('helpdesk_emails')->get($form->values->id)
            ->update([
                'subject' => $form->values->subject,
                'body' => $form->values->body,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditMailTemplateControl.latte');
        $template->render();
    }

}