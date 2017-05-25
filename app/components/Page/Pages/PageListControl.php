<?php
namespace Caloriscz\Page\Pages;

use Nette\Application\UI\Control;

class PageListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;
    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Delete page
     */
    function handleDelete($id)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->delete($id);
        \App\Model\IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->onSave($this->getParameter("type"));
    }

    function handlePublic()
    {
        $page = $this->database->table("pages")->get($this->getParameter("id"));

        if ($page->public == 1) {
            $show = 0;
        } else {
            $show = 1;
        }

        $this->database->table("pages")->get($this->getParameter("id"))->update(array("public" => $show));

        $this->onSave($this->getParameter("type"));
    }

    public function render($fileTemplate = "PageListControl")
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/' . $fileTemplate . '.latte');

        if ($this->presenter->template->type) {
            $type = $this->presenter->template->type;
        } else {
            $type = 9;
        }

        $template->type = $type;
        $template->pages = $this->database->table("pages")->where(array("pages_types_id" => $type))->order("title");

        $template->render();
    }

}
