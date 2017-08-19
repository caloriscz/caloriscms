<?php
namespace Caloriscz\Media\MediaForms;

use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;

class InsertMediaControl extends Control
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

        $form->addHidden('category');
        $form->addHidden('type');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea("preview", "dictionary.main.Description")
            ->setAttribute("class", "form-control");

        if ($this->presenter->getView() == 'albums') {
            $category = 6;
            $type = 6;
        } else {
            $category = 4;
            $type = 8;
        }

        $form->setDefaults(array(
            "category" => $category,
            "type" => $type
        ));

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        echo $form->values->category;

        $doc = new \App\Model\Document($this->database);
        $doc->setType($form->values->type);
        $doc->setTitle($form->values->title);
        $doc->setPreview($form->values->preview);
        $page = $doc->create($this->presenter->user->getId(), $form->values->category);

        \App\Model\IO::directoryMake(APP_DIR . '/media/' . $page);

        $this->presenter->redirect(this, array(
            "id" => $form->values->category,
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertMediaControl.latte');

        $template->render();
    }

}