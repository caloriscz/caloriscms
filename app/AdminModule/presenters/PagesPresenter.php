<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Pages presenter.
 */
class PagesPresenter extends BasePresenter
{
    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit page content
     */
    function createComponentEditSnippetForm()
    {
        $form = $this->baseFormFactory->createPH();
        $l = $this->presenter->getParameter("l");
        $snippet = $this->database->table("snippets")->get($this->getParameter("snippet"));

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
            $arr["l"] = $this->getParameter("l");
        }

        $arr["page_id"] = $this->getParameter("id");
        $arr["snippet_id"] = $this->getParameter("snippet");


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

        $this->redirect(":Admin:Pages:snippetsDetail", array(
            "id" => $form->values->page_id,
            "snippet" => $form->values->snippet_id,
            "l" => $form->values->l
        ));
    }

    /**
     * Edit page content
     */
    function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addText("title", "dictionary.main.Title")
            ->setRequired($this->translator->translate('messages.pages.NameThePage'));

        $form->setDefaults(array(
            "section" => $this->getParameter('id'),
        ));

        $form->addSubmit("submit", "dictionary.main.Create")
            ->setHtmlId('formxins');

        $form->onSuccess[] = $this->insertFormSucceeded;

        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new Model\Document($this->database);
        $doc->setType(1);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->user->getId());
        Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

        $this->redirect(":Admin:Pages:detail", array("id" => $page));
    }

    /**
     * Search related
     */
    protected function createComponentSearchRelatedForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('src', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->searchRelatedFormSucceeded;
        return $form;
    }

    public function searchRelatedFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->redirect(":Admin:Pages:detailRelated", array(
            "id" => $form->values->id,
            "src" => $form->values->src,
        ));
    }

    /**
     * Insert page content
     */
    function createComponentInsertSnippetForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id")
            ->setAttribute("class", "form-control");
        $form->addText("title", "dictionary.main.Title");

        $form->setDefaults(array(
            "id" => $this->getParameter('id'),
        ));

        $form->addSubmit("submit", "dictionary.main.Create")
            ->setHtmlId('formxins');

        $form->onSuccess[] = $this->insertSnippetFormSucceeded;
        $form->onValidate[] = $this->permissionValidated;

        return $form;
    }

    function insertSnippetFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("snippets")->insert(array(
            "keyword" => $form->values->title,
            "pages_id" => $form->values->id,
        ));

        $this->redirect(":Admin:Pages:snippets", array("id" => $form->values->id));
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->template->member->users_roles->pages_edit == 0) {
            $this->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->redirect(this);
        }
    }


    function handleChangeState($id, $public)
    {
        if ($public == 0) {
            $idState = 1;
        } else {
            $idState = 0;
        }

        $this->database->table("pages")->get($id)
            ->update(array(
                "public" => $idState,
            ));

        $this->redirect(":Admin:Pages:default", array("id" => null));
    }

    function handleDeleteRelated($id)
    {
        $this->database->table("pages_related")->get($id)->delete();
        $this->redirect(":Admin:Pages:detailRelated", array("id" => $this->getParameter("item")));
    }

    /**
     * Delete image
     */
    function handleDeleteImage($id)
    {
        $this->database->table("media")->get($id)->delete();

        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter("name"));
        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/tn/' . $this->getParameter("name"));

        $this->redirect(":Admin:Pages:detailImages", array("id" => $this->getParameter("name"),));
    }

    /**
     * Delete file
     */
    function handleDeleteFile($id)
    {
        $this->database->table("media")->get($id)->delete();

        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter("name"));

        $this->redirect(":Admin:Pages:detailFiles", array("id" => $this->getParameter("name"),));
    }

    function handleInsertRelated($id)
    {
        $this->database->table("pages_related")->insert(array(
            "pages_id" => $this->getParameter("item"),
            "related_pages_id" => $id,
        ));
        $this->redirect(":Admin:Pages:detailRelated", array("id" => $this->getParameter("item")));
    }

    /**
     * Delete page
     */
    function handleDelete($id)
    {
        $doc = new Model\Document($this->database);
        $doc->delete($id);
        Model\IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->redirect(":Admin:Pages:default", array("id" => null));
    }

    /**
     * Delete snippet
     */
    function handleDeleteSnippet($id)
    {
        $this->database->table("snippets")->get($id)->delete();

        $this->redirect(":Admin:Pages:snippets", array("id" => $this->getParameter("page")));
    }

    public function renderDefault()
    {
        $this->template->pages = $this->database->table("pages")->where(array("pages_types_id" => array(0, 1)))->order("title");
    }

    public function renderDetail()
    {
        $this->template->pages = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderSettings()
    {
        $this->template->pages = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderDetailImages()
    {
        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->images = $this->database->table("media")
            ->where(array("pages_id" => $this->getParameter("id"), "file_type" => 1));
    }

    public function renderDetailFiles()
    {
        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->files = $this->database->table("media")
            ->where(array("pages_id" => $this->getParameter("id"), "file_type" => 0));
    }

    public function renderSnippets()
    {
        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->snippets = $this->database->table("snippets")
            ->where(array("pages_id" => $this->getParameter("id")));
    }

    public function renderSnippetsDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->snippet = $this->database->table("snippets")->get($this->getParameter("snippet"));
    }

    public function renderDetailRelated()
    {
        $src = $this->getParameter("src");

        $this->template->relatedSearch = $this->database->table("pages")
            ->where(array("title LIKE ?" => '%' . $src . '%'))->limit(20);
        $this->template->related = $this->database->table("pages_related")
            ->where(array("pages_id" => $this->getParameter("id")));
        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
    }

}
