<?php

namespace App\Forms\Profile;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditAddressControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * orm: User fills in e-mail address to send e-mail with a password generator link
     * @return BootstrapUIForm
     */
    protected function createComponentEditAddressForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addHidden('contacts_id');
        $form->addText('name', 'dictionary.main.Name');
        $form->addText('street', 'dictionary.main.Street');
        $form->addText('zip', 'dictionary.main.ZIP');
        $form->addText('city', 'dictionary.main.City');

        $address = $this->database->table('contacts')->get($this->presenter->getParameter('id'));

        $form->setDefaults([
            'id' => $address->id,
            'name' => $address->name,
            'street' => $address->street,
            'zip' => $address->zip,
            'city' => $address->city,
        ]);
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editAddressFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editAddressFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('contacts')->where([
            'id' => $form->values->contacts_id,
        ])->update([
            'name' => $form->values->name,
            'street' => $form->values->street,
            'zip' => $form->values->zip,
            'city' => $form->values->city,
        ]);

        $this->presenter->redirect(':Front:Profile:address', ['id' => $form->values->id]);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditAddressControl.latte');
        $template->render();
    }

}
