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
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';

        $helpdeskEmailsDb = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));
        $templates = $this->database->table('helpdesk_templates');

        $form->addHidden('helpdesk_id');
        $form->addText('email', 'dictionary.main.Email');
        $form->addSelect('helpdesk_templates_id', 'Šablona', $templates->fetchPairs('id', 'title'))
        ->setAttribute('class', 'form-control');
        $form->addCheckbox('log', ' Ukládat e-maily do databáze');
        $form->addCheckbox('blacklist', ' Zapnout antispam');
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults([
            'helpdesk_email_id' => $this->presenter->getParameter('id'),
            'email' => $helpdeskEmailsDb->email,
            'helpdesk_templates_id' => $helpdeskEmailsDb->helpdesk_templates_id,
            'blacklist' => $helpdeskEmailsDb->blacklist,
            'log' => $helpdeskEmailsDb->log,
        ]);

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('helpdesk')->get($form->values->helpdesk_id)->update([
            'helpdesk_templates_id' => $form->values->helpdesk_templates_id,
            'email' => $form->values->email,
            'log' => $form->values->log,
            'blacklist' => $form->values->blacklist
        ]);

        $this->onSave($form->values->helpdesk_email_id);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditHelpdeskEmailSettingsControl.latte');
        $template->render();
    }

}