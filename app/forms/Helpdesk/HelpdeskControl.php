<?php

namespace App\Forms\Helpdesk;

use App\Model\Helpdesk;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

class HelpdeskControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSendForm()
    {
        $helpdesk = $this->database->table('helpdesk')->get(1);

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
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
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInName'), 'error');
            $this->presenter->redirect(':Front:Contact:default');
        }

        if (Validators::isEmail($form->values->email) === false) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInEmail'), 'error');
            $this->presenter->redirect(':Front:Contact:default');
        }

        if (strlen($form->values->message) < 2) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.fillInMessage'), 'error');
            $this->presenter->redirect(':Front:Contact:default');
        }

        // Validate by black list
        $blacklistDb = $this->database->table('blacklist')->fetchAll('title');

        foreach ($blacklistDb as $item) {
            if (strpos(strtolower($form->values->message), strtolower($item->title)) !== false) {
                $rings = true;
            }

            if (strpos(strtolower($form->values->name), strtolower($item->title)) !== false) {
                $rings = true;
            }
        }

        $helpdesk = $this->database->table('helpdesk')->get(1);

        if ($helpdesk->blacklist === 1 && isset($rings) && $rings) {
            $this->presenter->flashMessage('Zpráva obsahuje neplatné znaky', 'info');
            $this->presenter->redirect(':Front:Contact:default');
        }
    }

    /**
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

        $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(1);
        $helpdesk->setEmail($form->values->email);
        $helpdesk->setSettings($this->presenter->template->settings);
        $helpdesk->setParams($params);
        $send = $helpdesk->send();

        if ($send) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.thanksForMessage'), "success");
        } else {
            $this->presenter->flashMessage($this->presenter->translator->translate('E-mail nebyl odeslán'), "error");
        }

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/HelpdeskControl.latte');
        $this->template->render();
    }

}
