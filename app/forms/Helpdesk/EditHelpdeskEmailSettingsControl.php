<?php

namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditHelpdeskEmailSettingsControl extends Control
{

    /** @var Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send teste-mail
     */
    protected function createComponentEditForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';

        $helpdeskEmailsDb = $this->database->table('helpdesk_emails')->get($this->presenter->getParameter('id'));
        $templates = $this->database->table('helpdesk_templates');

        $form->addHidden('helpdesk_email_id');
        $form->addText('email', 'dictionary.main.Email');
        $form->addSelect('helpdesk_templates_id', 'Šablona', $templates->fetchPairs('id', 'title'))
        ->setAttribute('class', 'form-control');
        $form->addCheckbox('log', ' Ukládat e-maily do databáze');
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults(array(
            'helpdesk_email_id' => $this->presenter->getParameter('id'),
            'email' => $helpdeskEmailsDb->email,
            'helpdesk_templates_id' => $helpdeskEmailsDb->helpdesk_templates_id,
            'log' => $helpdeskEmailsDb->log,
        ));

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('helpdesk_emails')->get($form->values->helpdesk_email_id)->update(array(
            'helpdesk_templates_id' => $form->values->helpdesk_templates_id,
            'email' => $form->values->email,
            'log' => $form->values->log,
        ));

        $this->onSave($form->values->helpdesk_email_id);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditHelpdeskEmailSettingsControl.latte');
        $template->render();
    }

}