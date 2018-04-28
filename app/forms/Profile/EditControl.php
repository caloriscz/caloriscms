<?php

namespace Apps\Forms\Profile;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Validators;

/**
 * Member profile editor
 * @package Apps\Forms\Profile
 */
class EditControl extends Control
{

    /** @var Context */
    public $database;
    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit by user
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('email');
        $form->addText('name');
        $form->addCheckbox('adminbar')
            ->setDefaultValue($this->presenter->template->member->adminbar_enabled);
        $form->addSelect('language', '', ['cs' => 'česky', 'en' => 'English']);

        $form->setDefaults([
            'name' => $this->presenter->template->member->name,
            'email' => $this->presenter->template->member->email,
            'language' => $this->presenter->request->getCookie('language_admin')
        ]);

        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'validateFormSucceeded'];
        return $form;
    }

    public function validateFormSucceeded(BootstrapUIForm $form)
    {
        if (Validators::isEmail($form->values->email) === false) {
            $this->onSave('E-mail je povinný');
        }

        $userExists = $this->database->table('users')->where('email = ? AND NOT id = ?', $form->values->email, $this->presenter->user->getId());

        if ($userExists->count() > 0) {
            $this->onSave('E-mail již existuje');
        }
    }

    /**
     * Edit user settings by user
     * @param BootstrapUIForm $form
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('users')->get($this->presenter->user->getId())->update([
            'name' => $form->values->name,
            'email' => $form->values->email,
            'adminbar_enabled' => $form->values->adminbar,
        ]);

        $this->presenter->response->setCookie('language_admin', $form->values->language, '180 days');
    }

    public function render()
    {
        $template = $this->template;
        $template->member = $this->presenter->template->member->username;
        $template->setFile(__DIR__ . '/EditControl.latte');

        $template->render();
    }
}