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

        if ($this->presenter->getParameter('type') == '') {
            $pageType = 9;
        } else {
            $pageType = $this->presenter->getParameter('type');
        }

        $form->addHidden("id");
        $form->addHidden("section");
        $form->addText("title");

        $form->setDefaults(array(
            "section" => $pageType,
        ));

        $form->addSubmit("submit", "dictionary.main.Create")
            ->setHtmlId('formxins');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType($form->values->section);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->presenter->user->getId());


        \App\Model\IO::directoryMake(APP_DIR . '/media/' . $page, 0755);
        \App\Model\IO::directoryMake(APP_DIR . '/media/' . $page . "/tn", 0755);

        $this->presenter->redirect(":Admin:Pages:detail", array("id" => $page));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertFormControl.latte');

        $template->render();
    }

}