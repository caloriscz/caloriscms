<?php
namespace Caloriscz\Page\PageForms;

use Nette\Application\UI\Control;

class InsertFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addText("title", "dictionary.main.Title")
            ->setRequired($this->presenter->translator->translate('messages.pages.NameThePage'));

        $form->setDefaults(array(
            "section" => $this->presenter->getParameter('id'),
        ));

        $form->addSubmit("submit", "dictionary.main.Create")
            ->setHtmlId('formxins');

        $form->onSuccess[] = $this->insertFormSucceeded;

        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType(1);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->presenter->user->getId());
        \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

        $this->presenter->redirect(":Admin:Pages:detail", array("id" => $page));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertFormControl.latte');

        $template->render();
    }

}