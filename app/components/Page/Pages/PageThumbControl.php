<?php

namespace Caloriscz\Page\Pages;

use Nette\Application\UI\Control;

class PageThumbControl extends Control
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

    public function render($type, $id = "")
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/PageThumbControl.latte');

        if ($type == 6) {
            $type = 4;
        } else {
            $type = 6;
        }

        if ($id == "") {
            $arr = array(
                "pages_id" => $type,
            );
        } else {
            $arr = array(
                "pages_types_id" => $id,
                "pages_id" => $type,
            );
        }

        $template->pages = $this->database->table("pages")->where($arr);

        $template->render();
    }

}
