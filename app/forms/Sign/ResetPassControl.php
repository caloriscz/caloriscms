<?php

namespace App\Forms\Sign;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use App\Security\PasswordReset;

class ResetPassControl extends Control
{
    private const MIN_PASSWORD_LENGTH = 8;

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
        $form->addProtection('Platnost formuláře vypršela. Zkuste to znovu.');
        $form->addHidden('resetToken')->setHtmlAttribute('data-reset-token', '');
        $form->addPassword('password', 'Nové heslo')
            ->setRequired('Zadejte nové heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', self::MIN_PASSWORD_LENGTH);
        $form->addPassword('password2', 'Zopakujte nové heslo')
            ->setRequired('Zopakujte nové heslo.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);
        $form->addSubmit('name', 'Změnit');

        $form->onSuccess[] = [$this, 'resetFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function resetFormSucceeded(BootstrapUIForm $form): void
    {
        if ($form->values->password !== $form->values->password2) {
            $form->addError('Hesla se neshodují.');
            return;
        }
        if (!(new PasswordReset($this->database))->consume((string) $form->values->resetToken,
            $form->values->password)) {
            $form->addError('Odkaz je neplatný nebo prošlý, případně heslo nemá 8 až 72 bajtů. Požádejte o nový odkaz.');
            return;
        }
        $this->getPresenter()->getHttpResponse()->deleteCookie('calpwd', '/');
        $this->getPresenter()->flashMessage('Vytvořili jste nové heslo. Můžete se přihlásit.', 'success');
        $this->getPresenter()->redirect('Sign:in');
    }

    public function render(string $layer = 'front'): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ResetPassControl.latte');
        $template->layer = $layer;
        $template->member = $this->getPresenter()->template->member;
        $template->render();
    }

}
