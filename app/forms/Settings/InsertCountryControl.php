<?php
namespace App\Forms\Settings;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertCountryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('country_cs', 'Země (česky)');
        $form->addText('country_en', 'Země (anglicky)');

        $form->addSubmit('send', 'Uložit')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function permissionValidated(): void
    {
        if ($this->presenter->template->member->users_roles->settings == 0) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->redirect('this');
        }
    }


    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $exists = $this->database->table('countries')->where('title_cs = ? OR title_en = ?',
            $form->values->country_cs, $form->values->country_en);

        if ($exists->count() > 0) {
            $this->flashMessage('Země už je v seznamu', 'error');
            $this->redirect(this);
        } else {
            $this->database->table('countries')->insert([
                'title_cs' => $form->values->country_cs,
                'title_en' => $form->values->country_en,
            ]);

            $this->redirect('this');
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertCountryControl.latte');

        $template->render();
    }

}