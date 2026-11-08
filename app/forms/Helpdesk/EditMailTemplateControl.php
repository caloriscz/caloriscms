<?php
namespace App\Forms\Helpdesk;

use App\Model\Helpdesk;
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
        if ($emailDb === null) {
            $this->presenter->error('Record not found.', 404);
        }

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
        $helpdesk = $this->database->table('helpdesk')->get($form->values->id);
        if ($helpdesk === null) {
            $this->presenter->error('Record not found.', 404);
        }
        // Subject is stored as typed; body is trusted Latte source (see Helpdesk::templateError).
        $subject = $form->values->subject;
        $body = Helpdesk::normalizeTemplateSource($form->values->body);
        $frame = $this->database->table('helpdesk_templates')->get($helpdesk->helpdesk_templates_id ?: 1);
        $error = Helpdesk::subjectError($subject)
            ?? Helpdesk::templateError($body, Helpdesk::composeDocument($frame ? $frame->document : '%TITLE%%CONTENT%', $subject, $body));
        if ($error !== null) {
            $form->addError($error);
            return;
        }

        $helpdesk->update([
                'subject' => $subject,
                'body' => $body,
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
