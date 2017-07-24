<?php

namespace Caloriscz\Page\Filters;

use Nette\Application\UI\Control;

class SearchControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Filter
     */
    protected function createComponentSearchTopForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();

        $form->setMethod("GET");
        $form->getElementPrototype()->class = "form-inline";

        $form->addHidden('idr');
        $form->addText('src')
            ->setAttribute("class", "form-control")
            ->setAttribute("placeholder", "Hledat");
        $form->addText("priceFrom")
            ->setAttribute("style", "width: 50px;");
        $form->addText("priceTo")
            ->setAttribute("style", "width: 50px;");
        $form->addText("brand");

        if ($this->getParameter("id")) {
            $form->setDefaults(array(
                "idr" => $this->getParameter("id"),
            ));
        }

        $form->addSubmit('submitm', 'dictionary.main.Search')
            ->setAttribute("class", "btn btn-info btn-lg")
            ->setAttribute("placeholder", "dictionary.main.Search");


        $form->onSuccess[] = [$this, "searchTopFormSucceeded"];
        return $form;
    }

    public function searchTopFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getValues(TRUE);
        unset($values["do"], $values["action"], $values["idr"]);

        $this->presenter->redirect(":Front:Catalogue:default", $values);
    }

    public function render()
    {
        $template = $this->template;
        $template->isLoggedIn = $this->presenter->template->isLoggedIn;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/SearchControl.latte');

        $template->render();
    }

}
