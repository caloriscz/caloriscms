<?php

namespace App\Forms\Sign;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\Passwords;

class ResetPassControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: Resets passwords in database, user fill in new password
     */
    protected function createComponentResetForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('email');
        $form->addHidden('code');
        $form->addPassword('password', 'Nové heslo');
        $form->addPassword('password2', 'Zopakujte nové heslo');
        $form->addSubmit('name', 'dictionary.main.Change');
        $form->setDefaults([
            'email' => $this->getPresenter()->getParameter('email'),
            'code' => $this->getPresenter()->getParameter('code'),
        ]);

        $form->onSuccess[] = [$this, 'resetFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function resetFormSucceeded(BootstrapUIForm $form)
    {
        $email = $form->values->email;
        $emailExistsDb = $this->database->table('users')->where([
            'email' => $email,
            'activation' => $form->values->code,
        ]);

        if ($emailExistsDb->count() === 0) {
            $msg = 'Aktivace není platná';
        } elseif (strcmp($form->getValues()->password, $form->getValues()->password2) <> 0) {
            $msg = 'Hesla se neshodují';
        } else {
            $msg = 'Vytvořili jste nové heslo. Můžete se přihlásit.';
            $this->database->table('users')->where([
                'email' => $email,
            ])->update([
                'activation' => NULL,
                'password' => Passwords::hash($form->getValues()->password),
            ]);
        }

        $this->getPresenter()->flashMessage($msg, 'success');
        $this->getPresenter()->redirect('Sign:in');
    }

    public function render(string $layer = 'front')
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ResetPassControl.latte');
        $template->layer = $layer;
        $template->member = $this->getPresenter()->template->member;
        $template->render();
    }

}
