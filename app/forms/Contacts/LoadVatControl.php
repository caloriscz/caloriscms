<?php
namespace App\Forms\Contacts;

use h4kuna\Ares\Ares;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class LoadVatControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit contact
     */
    protected function createComponentLoadForm()
    {
        $this->template->id = $this->presenter->getParameter('id');

        $contact = $this->database->table('contacts')->get($this->presenter->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('contact_id');
        $form->addHidden('pages_id');

        $form->addText('vatin', '')
            ->setAttribute('placeholder', 'dictionary.main.VatIn');

        $form->setDefaults([
            'contact_id' => $contact->id,
            'pages_id' => $this->presenter->getParameter('id'),
        ]);

        $form->addSubmit('submitm', 'Načíst')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'loadVatFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     * @throws \h4kuna\Ares\IdentificationNumberNotFoundException
     */
    public function loadVatFormSucceeded(BootstrapUIForm $form): void
    {
        if ($form->values->vatin) {
            $ares = new Ares();
            $aresArr = $ares->loadData(str_replace(' ', '', $form->values->vatin))->toArray();

            if (count($aresArr) > 0) {
                $this->database->table('contacts')
                    ->where([
                        'id' => $form->values->contact_id,
                    ])
                    ->update([
                        'name' => $aresArr['company'],
                        'street' => $aresArr['street'],
                        'zip' => $aresArr['zip'],
                        'city' => $aresArr['city'],
                        'vatin' => $aresArr['in'],
                        'vatid' => $aresArr['tin'],
                    ]);
            } else {
                $this->presenter->flashMessage($this->translator->translate('messages.sign.NotFound'), 'error');
            }
        } else {
            $this->presenter->flashMessage($this->translator->translate('messages.sign.NotFound'), 'error');
        }

        $this->presenter->redirect('this', ['id' => $form->values->pages_id]);
    }

    /**
     *
     */
    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/LoadVatControl.latte');

        $template->render();
    }

}