<?php

namespace App\Forms\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditHelpdeskEmailSettingsControl extends Control
{

    public Explorer $database;

    public $onSave;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Send teste-mail
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $templates = $this->database->table('helpdesk_templates');
        $pages = $this->database->table('pages');
        $pagesList = $pages->fetchPairs('id', 'title');
        array_unshift($pagesList, null);

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->addHidden('helpdesk_id');
        $form->addText('email', 'E-mail');
        $form->addSelect('helpdesk_templates_id', 'Šablona', $templates->fetchPairs('id', 'title'))
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('pages_id', 'Výběr stránek', $pagesList)
            ->setHtmlAttribute('class', 'form-control');
        $form->addCheckbox('log', ' Ukládat e-maily do databáze');
        $form->addCheckbox('blacklist', ' Zapnout antispam');
        $form->addSubmit('submitm', 'Uložit');

        $helpdeskEmailsDb = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));

        if ($helpdeskEmailsDb !== null) {
            $form->setDefaults([
                'helpdesk_id' => $helpdeskEmailsDb->id,
                'email' => $helpdeskEmailsDb->email,
                'helpdesk_templates_id' => $helpdeskEmailsDb->helpdesk_templates_id,
                'pages_id' => $helpdeskEmailsDb->pages_id,
                'blacklist' => $helpdeskEmailsDb->blacklist,
                'log' => $helpdeskEmailsDb->log,
            ]);
        }

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $page = $form->values->pages_id;

        if ($page === 0) {
            $page = null;
        }

        $this->database->table('helpdesk')->get($form->values->helpdesk_id)->update([
            'helpdesk_templates_id' => $form->values->helpdesk_templates_id,
            'pages_id' => $page,
            'email' => $form->values->email,
            'log' => $form->values->log,
            'blacklist' => $form->values->blacklist
        ]);

        $this->onSave($form->values->helpdesk_templates_id);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditHelpdeskEmailSettingsControl.latte');
        $template->render();
    }

}