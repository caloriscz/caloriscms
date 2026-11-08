<?php

namespace App\Forms\Helpdesk;

use App\Model\Helpdesk;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Http\Url;
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
        $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(1);
        $error = null;
        if (strlen($form->values->name) < 2) { $error = 'Vyplňte jméno'; }
        elseif (!Validators::isEmail($form->values->email)) { $error = 'Vyplňte e-mail'; }
        elseif (strlen($form->values->message) < 2) { $error = 'Vyplňte zprávu'; }
        if ($error === null) {
            try {
                $info = $helpdesk->getInfo();
                if (!$info) {
                    $helpdesk->reportFailure('validation', 'missing_helpdesk');
                    $error = 'E-mail nebyl odeslán';
                } elseif ((int) $info->blacklist === 1) {
                    foreach ($this->database->table('blacklist') as $item) {
                        if ($item->title !== '' && (stripos($form->values->message, $item->title) !== false
                            || stripos($form->values->name, $item->title) !== false)) {
                            $error = 'Zpráva obsahuje neplatné znaky';
                            break;
                        }
                    }
                }
            } catch (\Throwable $failure) {
                $helpdesk->reportFailure('validation', 'unavailable');
                $error = 'E-mail nebyl odeslán';
            }
        }
        if ($error !== null) {
            $form->addError($error);
            $this->redirectWithFeedback($helpdesk, $error, 'error');
        }

    }

    /**
     * redirectUrl() does not carry the flash key (_fid) the way redirect() does,
     * so the return page would never see the message. Append it to the internal
     * return path; the target itself is unchanged.
     * @throws \Nette\Application\AbortException
     */
    private function redirectWithFeedback(Helpdesk $helpdesk, string $message, string $type): void
    {
        $this->presenter->flashMessage($message, $type)->helpdesk = true;
        $url = new Url($helpdesk->getReturnPath());
        $url->setQueryParameter(Presenter::FLASH_KEY, $this->presenter->getParameter(Presenter::FLASH_KEY));
        $this->presenter->redirectUrl((string) $url);
    }

    /**
     * Sends e-mail from web form
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function sendFormSucceeded(BootstrapUIForm $form): void
    {
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
            $this->redirectWithFeedback($helpdesk, 'Děkujeme za zprávu', 'success');
        } else {
            $this->redirectWithFeedback($helpdesk, 'E-mail nebyl odeslán', 'error');
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
