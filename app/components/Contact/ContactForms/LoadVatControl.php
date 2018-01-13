<?php
namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class LoadVatControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit contact
     */
    function createComponentLoadForm()
    {
        $this->template->id = $this->presenter->getParameter('id');

        $contact = $this->database->table("contacts")->get($this->presenter->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('contact_id');
        $form->addHidden('pages_id');

        $form->addText("vatin", "")
            ->setAttribute("placeholder", "dictionary.main.VatIn");

        $form->setDefaults(array(
            "contact_id" => $contact->id,
            "pages_id" => $this->presenter->getParameter("id"),
        ));

        $form->addSubmit("submitm", "Načíst")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = [$this, 'loadVatFormSucceeded'];
        return $form;
    }

    function loadVatFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->vatin) {
            $ares = new \h4kuna\Ares\Ares();
            $aresArr = $ares->loadData(str_replace(" ", "", $form->values->vatin))->toArray();

            if (count($aresArr) > 0) {
                $this->database->table("contacts")
                    ->where(array(
                        "id" => $form->values->contact_id,
                    ))
                    ->update(array(
                        "name" => $aresArr['company'],
                        "street" => $aresArr['street'],
                        "zip" => $aresArr['zip'],
                        "city" => $aresArr['city'],
                        "vatin" => $aresArr['in'],
                        "vatid" => $aresArr['tin'],
                    ));
            } else {
                $this->presenter->flashMessage($this->translator->translate('messages.sign.NotFound'), "error");
            }
        } else {
            $this->presenter->flashMessage($this->translator->translate('messages.sign.NotFound'), "error");
        }

        $this->presenter->redirect(this, array("id" => $form->values->pages_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LoadVatControl.latte');

        $template->render();
    }

}