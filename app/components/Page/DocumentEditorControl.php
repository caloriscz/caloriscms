<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;

class DocumentEditorControl extends Control
{

    private $htmlPurifier;

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $this->htmlPurifier = new \HtmlPurifier($config);
    }

    /**
     * Edit page content
     */
    function createComponentEditForm()
    {
        $pages = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $l = $this->presenter->getParameter("l");

        $form->addHidden("id");
        $form->addHidden("l");
        $form->addHidden("docs_id");
        $form->addTextArea("document")
            ->setAttribute("class", "form-control")
            ->setHtmlId('wysiwyg');

        if ($l == '') {
            $form->setDefaults(array(
                "id" => $pages->id,
                "document" => $pages->document,
            ));
        } else {
            $form->setDefaults(array(
                "id" => $pages->id,
                "l" => $l,
                "document" => $pages->{'document_' . $l},
            ));
        }

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->addSubmit("submit", "dictionary.main.Save")
            ->setHtmlId('formxins');

        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setLanguage($form->values->l);

        //$document = $this->purify($form->values->document);

        $doc->setDocument($form->values->document);
        $doc->save($form->values->id, $this->presenter->user->getId());

        $this->presenter->redirect(this, array("id" => $form->values->id, "l" => $form->values->l));
    }

    public function purify($dirtyHtml)
    {
        return $this->htmlPurifier->purify($dirtyHtml);
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/DocumentEditorControl.latte');

        $template->render();
    }

}
