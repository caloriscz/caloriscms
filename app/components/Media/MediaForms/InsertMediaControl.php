<?php

namespace Caloriscz\Media\MediaForms;

use App\Model\Document;
use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Diagnostics\Debugger;
use Nette\Forms\BootstrapUIForm;

class InsertMediaControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('category');
        $form->addHidden('type');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea('preview', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');

        if ($this->presenter->getView() == 'albums') {
            $category = 4;
            $type = 6;
        } else {
            $category = 6;
            $type = 8;
        }

        if ($this->getPresenter()->getParameter('id')) {
            $category = $this->getPresenter()->getParameter('id');
        }

        $form->setDefaults([
            'category' => $category,
            'type' => $type
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $doc = new Document($this->database);
        $doc->setType($form->values->type);
        $doc->setTitle($form->values->title);
        $doc->setPreview($form->values->preview);
        $page = $doc->create($this->getPresenter()->user->getId(), $form->values->category);

        IO::directoryMake(APP_DIR . '/media/' . $page);

        $this->getPresenter()->redirect(this, array(
            'id' => $form->values->category,
        ));
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertMediaControl.latte');
        $this->template->render();
    }

}