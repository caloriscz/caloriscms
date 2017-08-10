<?php

namespace Caloriscz\Page\Editor;

use Nette\Application\UI\Control;

class EditorControl extends Control
{
    private $htmlPurifier;

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $this->htmlPurifier = new \HtmlPurifier($config);
    }

    protected function createComponentLangSelector()
    {
        $control = new \LangSelectorControl($this->database);
        return $control;
    }

    /**
     * Edit page content
     */
    function createComponentEditForm()
    {
        $pages = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $l = $this->presenter->getParameter("l");

        if ($this->presenter->template->member->users_roles->pages_document) {
            $enabled = false;
        } else {
            $enabled = true;
        }

        $form->addHidden("id");
        $form->addHidden("l");
        $form->addHidden("docs_id");
        $form->addTextArea("document")->setDisabled($enabled);

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

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionFormValidated'];
        $form->addSubmit("submit", "dictionary.main.Save")
            ->setHtmlId('formxins');

        return $form;
    }

    function permissionFormValidated()
    {
        if ($this->presenter->template->member->users_roles->pages_edit == 0) {
            $this->presenter->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setLanguage($form->values->l);

        //$document = $this->purify($form->values->document);

        $doc->setDocument($form->values->document);
        $doc->setLanguage($form->values->l);
        $doc->save($form->values->id, $this->presenter->user->getId());

        $this->presenter->redirect(this, array("id" => $form->values->id, "l" => $form->values->l));
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        setcookie("editortype", $this->getParameter("editortype"), time() + 15552000);

        $this->presenter->redirect(this, array("id" => $this->getParameter("id")));
    }

    /************************************/

    public function purify($dirtyHtml)
    {
        return $this->htmlPurifier->purify($dirtyHtml);
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->editortype = $_COOKIE["editortype"];

        $template->pages = $this->database->table("pages")->where("NOT id", $this->presenter->getParameter("id"));
        $template->page = $this->database->table("pages")->get($this->presenter->getParameter("id"));

        $template->templates = $this->database->table("pages_templates")->where("pages_types_id IS NULL")->order("title");

        if ($this->presenter->template->member->users_roles->pages_document) {
            $template->enabled = true;
        } else {
            $template->enabled = false;
        }

        $template->page_id = $this->presenter->getParameter("id");

        $template->setFile(__DIR__ . '/EditorControl.latte');

        $template->render();
    }

}