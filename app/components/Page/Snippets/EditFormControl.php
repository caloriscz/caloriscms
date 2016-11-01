<?php
namespace Caloriscz\Page\Snippets;

use Nette\Application\UI\Control;

class EditFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit page content
     */
    function createComponentEditSnippetForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $l = $this->presenter->getParameter("l");
        $snippet = $this->database->table("snippets")->get($this->presenter->getParameter("snippet"));

        $form->addHidden("page_id");
        $form->addHidden("snippet_id");
        $form->addHidden("pages_id");
        $form->addHidden("l");
        $form->addTextArea("content")
            ->setAttribute("class", "form-control")
            ->setAttribute("height", "250px")
            ->setHtmlId('wysiwyg-sm');

        if ($l == '') {
            $arr["content"] = $snippet->content;
        } else {
            $arr["content"] = $snippet->{'content_' . $l};
            $arr["l"] = $this->presenter->getParameter("l");
        }

        $arr["page_id"] = $this->presenter->getParameter("id");
        $arr["snippet_id"] = $this->presenter->getParameter("snippet");


        $form->setDefaults($arr);

        $form->onSuccess[] = $this->editSnippetFormSucceeded;
        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success")
            ->setHtmlId('formxins');

        return $form;
    }

    function editSnippetFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $content = $form->getHttpData($form::DATA_TEXT, 'content');

        if ($form->values->l != '') {
            $langSuffix = '_' . $form->values->l;
        }

        $this->database->table("snippets")->get($form->values->snippet_id)->update(array(
            "content" . $langSuffix => $content,
        ));

        $this->presenter->redirect(this, array(
            "id" => $form->values->page_id,
            "snippet" => $form->values->snippet_id,
            "l" => $form->values->l
        ));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditFormControl.latte');

        $template->render();
    }

}