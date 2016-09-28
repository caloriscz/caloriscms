<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class BlogPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
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
    
    protected function createComponentInsertBlogForm()
    {
        $control = new \Caloriscz\Blog\BlogForms\InsertFormControl($this->database);
        return $control;
    }
    
    protected function createComponentPageFilterRelated()
    {
        $control = new \Caloriscz\Page\Related\FilterFormControl($this->database);
        return $control;
    }

    /**
     * Delete post
     */
    function handleDelete($id)
    {
        Model\IO::removeDirectory(APP_DIR . '/media/' . $id);
        $this->database->table("pages")->get($id)->delete();

        $this->redirect(":Admin:Blog:default", array("id" => null));
    }
    
    /**
     * Delete snippet
     */
    function handleDeleteSnippet($id)
    {
        $this->database->table("snippets")->get($id)->delete();

        $this->redirect(":Admin:Blog:snippets", array("id" => $this->getParameter("page")));
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

        $this->redirect(":Admin:Blog:default", array("id" => null));
    }

    function handleDeleteRelated($id)
    {
        $this->database->table("pages_related")->get($id)->delete();
        $this->redirect(":Admin:Blog:detailRelated", array("id" => $this->getParameter("item")));
    }

    function handleInsertRelated($id)
    {
        $this->database->table("pages_related")->insert(array(
            "pages_id" => $this->getParameter("item"),
            "related_pages_id" => $id,
        ));
        $this->redirect(":Admin:Blog:detailRelated", array("id" => $this->getParameter("item")));
    }

    public function renderDefault()
    {
        if ($this->getParameter("id") == null) {
            $blog = $this->database->table("pages")->where(array("pages_types_id" => 2));
        } else {
            $blog = $this->database->table("pages")
                ->where("categories_id = ? AND content_type = 2", $this->getParameter("id"));
        }

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($blog->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->blog = $blog->order("date_published DESC")->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();
    }

    public function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $slugParam = $this->getParameter("slug");

        if ($slugParam) {
            $slugDb = $this->database->table("pages")->where(array("title" => $slugParam));

            if ($slugDb->count() > 0) {
                $this->redirect(":Admin:Blog:detail", array("id" => $slugDb->fetch()->id));
            } else {
                $this->redirect(":Admin:Blog:default");
            }
        }

        $this->template->blog = $this->database->table("pages")->where(array("id" => $this->getParameter("id")))->fetch();
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


    public function renderDetailFiles()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->files = $this->database->table("media")
            ->where(array("pages_id" => $this->getParameter("id"), "file_type" => 0));
    }

    public function renderDetailImages()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderDetailRelated()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $src = $this->getParameter("src");

        $this->template->relatedSearch = $this->database->table("pages")
            ->where(array("title LIKE ?" => '%' . $src . '%'))->limit(20);
        $this->template->related = $this->database->table("pages_related")
            ->where(array("pages_id" => $this->getParameter("id")));
        $this->template->catalogue = $this->database->table("pages")->get($this->getParameter("id"));
    }

}