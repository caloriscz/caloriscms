<?php
namespace Caloriscz\Settings\Language;

use Nette\Application\UI\Control;

class LanguageSelectorForAdminControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /* Insert new language */
    function createComponentChangeForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);

        $form->addSelect("language", "Jazyk", array("cs" => "česky", "en" => "English"))
            ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "language" => $this->presenter->translator->getLocale()
        ));

        $form->addSubmit('submitm', 'dictionary.main.Change')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = [$this, "changeFormSucceeded"];
        return $form;
    }

    function changeFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->presenter->response->setCookie('language_admin', $form->values->language, '180 days');

        $this->presenter->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LanguageSelectorForAdminControl.latte');

        $template->render();
    }

}