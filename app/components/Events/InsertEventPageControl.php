<?php
namespace Caloriscz\Events;

use Nette\Application\UI\Control;

class InsertEventPageControl extends Control
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

        $form->addText('title', 'dictionary.main.Title');

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setType(3);
        $doc->setTitle($form->values->title);
        $doc->setSlug($form->values->title);
        $id = $doc->create($this->presenter->user->getId());

        \App\Model\IO::directoryMake(APP_DIR . '/media/' . $id);

        $this->presenter->redirect(":Admin:Events:detail", array(
            "id" => $id,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertEventPageControl.latte');

        $template->render();
    }

}