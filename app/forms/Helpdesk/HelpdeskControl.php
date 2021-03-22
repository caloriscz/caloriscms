<?php

namespace App\Forms\Helpdesk;

use App\Model\Helpdesk;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

class HelpdeskControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentSendForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('name');
        $form->addText('email');
        $form->addText('phone');
        $form->addTextArea('message');

        $form->setDefaults([
            'name' => $this->getParameter('name'),
            'email' => $this->getParameter('email'),
            'phone' => $this->getParameter('phone'),
            'message' => $this->getParameter('message'),
        ]);
        $form->addSubmit('submitm');

        $form->onValidate[] = [$this, 'sendFormValidated'];
        $form->onSuccess[] = [$this, 'sendFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function sendFormValidated(BootstrapUIForm $form): void
    {
        if (strlen($form->values->name) < 2) {
            $this->presenter->flashMessage('Vyplňte jméno', 'error');
            $this->presenter->redirect('this');
        }


        if (Validators::isEmail($form->values->email) === false) {
            $this->presenter->flashMessage('Vyplňte e-mail', 'error');
            $this->presenter->redirect('this');
        }

        if (strlen($form->values->message) < 2) {
            $this->presenter->flashMessage('Vyplňte zprávu', 'error');
            $this->presenter->redirect('this');
        }

        // Validate by black list
        $blacklistDb = $this->database->table('blacklist')->fetchAll('title');

        foreach ($blacklistDb as $item) {
            if (stripos($form->values->message, $item->title) !== false) {
                $rings = true;
            }

            if (stripos($form->values->name, $item->title) !== false) {
                $rings = true;
            }
        }

        $helpdesk = $this->database->table('helpdesk')->get(1);

        if ($helpdesk === null) {
            $this->presenter->flashMessage('Neznámá chyba', 'info');
            $this->presenter->redirect('this');
        }

        if ($helpdesk->blacklist === 1 && isset($rings) && $rings) {
            $this->presenter->flashMessage('Zpráva obsahuje neplatné znaky', 'info');
            $this->presenter->redirect('this');
        }
    }

    /**
     * Sends e-mail from web form
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function sendFormSucceeded(BootstrapUIForm $form): void
    {
        if ($this->presenter->user->isLoggedIn()) {
            $arr['users_id'] = $this->presenter->template->member->id;
        }

        $params = [
            'name' => $form->values->name,
            'phone' => $form->values->phone,
            'message' => $form->values->message,
        ];

        // Helpdesk - send to admin
        $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(1);
        $helpdesk->setEmail($form->values->email);
        $helpdesk->setSettings($this->presenter->template->settings);
        $helpdesk->setParams($params);
        $send = $helpdesk->send();

        if ($send) {
            $this->flashMessage('Děkujeme za zprávu', 'success');
        } else {
            $this->flashMessage('E-mail nebyl odeslán', 'error');
        }

        $helpdeskPage = $helpdesk->getInfo()->pages->slug;

        if ($helpdeskPage !== null) {
            $this->presenter->redirectUrl('/' . $helpdeskPage);
        } else {
            die();
        }
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/HelpdeskControl.latte');
        $this->template->render();
    }

}
