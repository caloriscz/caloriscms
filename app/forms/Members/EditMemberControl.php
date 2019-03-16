<?php
namespace App\Forms\Members;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditMemberControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
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
            $form->addSelect('role', 'Role', $roles)
                ->setAttribute('class', 'form-control');
        }

        $arr = [
            'id' => $this->presenter->getParameter('id'),
            'state' => $user->state,
            'role' => $user->role,
            'group' => $user->categories_id,
        ];

        $form->setDefaults(array_filter($arr));

        $form->addSubmit('submitm', 'Uložit')->setAttribute('class', 'btn btn-primary');

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
        if (!$this->presenter->template->member->users_roles->members) {
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
        $arr = [
            'sex' => $form->values->sex,
            'newsletter' => $form->values->newsletter,
            'state' => $form->values->state,
        ];

        if ($this->presenter->template->member->username) {
            $arr['users_roles_id'] = $form->values->role;
        }

        $this->database->table('users')->where(['id' => $form->values->id])->update($arr);
        $this->presenter->redirect('this', ['' => $form->values->id]);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/EditMemberControl.latte');
        $this->template->render();
    }

}
