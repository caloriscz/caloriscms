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
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('pages_id', $this->getParameter('id'));
        $form->addRadioList('type', 'Osoba nebo organizace', [0 => ' osoby', 1 => ' organizace']);
        $form->addText('title', 'dictionary.main.Title')
            ->setRequired($this->presenter->translator->translate('messages.pages.NameThePage'));

        $form->setDefaults(array(
            'type' => 0
        ));

        $form->addSubmit('submitm', 'dictionary.main.CreateNewContact')
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

        if ($form->values->type === 0) {
            $arr['name'] = $form->values->title;
        } else {
            $arr['company'] = $form->values->title;
        }

        $id = $this->database->table('contacts')->insert($arr);

        $this->presenter->redirect(':Admin:Contacts:detail', ['id' => $id]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertContactControl.latte');
        $this->template->render();
    }

}
