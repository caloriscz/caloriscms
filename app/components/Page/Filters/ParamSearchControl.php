<?php
namespace Caloriscz\Page\Filters;

use Nette\Application\UI\Control;

class ParamSearchControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearchForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->setMethod("GET");

        $form->addHidden('idr');
        $form->addHidden('src');
        $form->addHidden("priceFrom");
        $form->addHidden("priceTo");
        $form->addHidden("brand");

        $form->setDefaults(array(
            "idr" => $this->presenter->getParameter("id"),
            "src" => $this->presenter->getParameter("src"),
            "priceFrom" => $this->presenter->getParameter("priceFrom"),
            "PriceTo" => $this->presenter->getParameter("priceTo"),
            "brand" => $this->presenter->getParameter("brand"),
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-info btn-lg");


        $form->onSuccess[] = $this->searchFormSucceeded;
        return $form;
    }

    public function searchFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input
        unset($values["do"], $values["action"], $values["idr"]);

        $this->presenter->redirect(":Front:Catalogue:default", $values);
    }

    public function render()
    {
        $template = $this->template;
        $template->database = $this->database;

        if ($this->presenter->translator->getLocale() != $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
        }

        $params = $this->database->table("param")->where(array(
            ":param_categories.pages_id" => $this->presenter->getParameter("page_id"),
        ));
        $template->params = $params;

        $template->setFile(__DIR__ . '/ParamSearchControl.latte');

        $template->render();
    }

}
