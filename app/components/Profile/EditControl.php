<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Edit your profile
     */
    protected function createComponentEditForm()
    {
        $gender = 1;

        if (!$gender) {
            $gender = $this->presenter->template->member->sex;
        }

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);

        $form->addText('username');
        $form->addRadioList('sex', '', ['1' => '\xC2\xA0' . 'žena', '2' => '\xC2\xA0' . 'muž']);

        $form->setDefaults([
            'username' => $this->presenter->template->member->username,
            'sex' => $gender,
        ]);

        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('users')->where(['id' => $this->presenter->user->getId()])
            ->update(['sex' => $form->values->sex]);

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditControl.latte');
        $this->template->render();
    }

}
