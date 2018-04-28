<?php
namespace Caloriscz\Events;

use App\Model\Document;
use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertEventPageControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'dictionary.main.Title');

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    function insertFormSucceeded(BootstrapUIForm $form)
    {
        $doc = new Document($this->database);
        $doc->setType(3);
        $doc->setTitle($form->values->title);
        $doc->setSlug($form->values->title);
        $id = $doc->create($this->presenter->user->getId());

        IO::directoryMake(APP_DIR . '/media/' . $id);

        $this->presenter->redirect(":Admin:Events:detail", array(
            "id" => $id,
        ));
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/InsertEventPageControl.latte');
        $template->render();
    }

}