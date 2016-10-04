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

    protected function createComponentEditSnippetForm()
    {
        $control = new \Caloriscz\Page\Snippets\EditFormControl($this->database);
        return $control;
    }

    protected function createComponentInsertSnippetForm()
    {
        $control = new \Caloriscz\Page\Snippets\InsertFormControl($this->database);
        return $control;
    }

    protected function createComponentPageFilterRelated()
    {
        $control = new \Caloriscz\Page\Related\FilterFormControl($this->database);
        return $control;
    }

    protected function createComponentInsertPageForm()
    {
        $control = new \Caloriscz\Page\PageForms\InsertFormControl($this->database);
        return $control;
    }

    protected function createComponentImageEditForm()
    {
        $control = new \Caloriscz\Media\MediaForms\ImageEditFormControl($this->database);
        return $control;
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

    public function renderImagesDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
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
