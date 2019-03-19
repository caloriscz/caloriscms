<?php

namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertContactControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Insert contact
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('pages_id', $this->getParameter('id'));
        $form->addRadioList('type', 'Osoba nebo organizace', [0 => ' osoba', 1 => ' organizace']);
        $form->addText('title', 'Název')
            ->setRequired('Zadejte název');

        $form->setDefaults(['type' => 0]);

        $form->addSubmit('submitm', 'Vytvořit nový kontakt')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $arr = [
            'users_id' => null,
            'type' => $form->values->type,
        ];

        $arr['name'] = $form->values->title;
        $id = $this->database->table('contacts')->insert($arr);

        $this->presenter->redirect(':Admin:Contacts:detail', ['id' => $id]);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/InsertContactControl.latte');
        $this->template->render();
    }

}
