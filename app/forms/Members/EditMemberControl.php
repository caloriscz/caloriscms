<?php
namespace App\Forms\Members;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditMemberControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit user data
     * @return BootstrapUIForm
     */
    public function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $user = $this->database->table('users')->get($this->presenter->getParameter('id'));

        $form->addHidden('id');

        $roles = $this->database->table('users_roles')->fetchPairs('id', 'title');

        if ($this->presenter->template->member->username === 'admin') {
            $form->addSelect('role', 'Role', $roles)->setHtmlAttribute('class', 'form-control');
        }

        $arr = [
            'id' => $this->presenter->getParameter('id'),
            'state' => $user->state,
            'role' => $user->users_roles_id
        ];

        $form->setDefaults(array_filter($arr));

        $form->addSubmit('submitm', 'Uložit')->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'editFormValidated'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormValidated(BootstrapUIForm $form): void
    {
        if (!$this->presenter->template->memberRole || !$this->presenter->template->memberRole->members) {
            $this->presenter->flashMessage('Přístup zamítnut', 'error');
            $this->presenter->redirect('this', ['id' => $form->values->id]);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getValues(true);
        $arr = [];

        foreach (['sex', 'newsletter', 'state'] as $field) {
            if (array_key_exists($field, $values)) {
                $arr[$field] = $values[$field];
            }
        }

        if ($this->presenter->template->member->username === 'admin' && array_key_exists('role', $values)) {
            $arr['users_roles_id'] = $values['role'];
        }

        if ($arr) {
            $this->database->table('users')->where(['id' => $values['id']])->update($arr);
        }

        $this->presenter->redirect('this', ['id' => $values['id']]);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/EditMemberControl.latte');
        $this->template->render();
    }
}
