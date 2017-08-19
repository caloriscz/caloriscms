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

    protected function createComponentMenuInsert()
    {
        $control = new \Caloriscz\Menus\MenuForms\InsertMenuControl($this->database);
        return $control;
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

    public function render($type = 1, $fileTemplate = "PageListControl")
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/' . $fileTemplate . '.latte');

        if ($type == 2) {
            $order = "title";
        } else {
            $order = "FIELD(id, 1, 3, 4, 6, 2), title";
        }

        $template->menu = $this->database->table("pages")->where('pages_id', null)->order($order);

        $template->type = $type;
        $template->database = $this->database;
        $template->pages = $this->database->table("pages")->where(array("pages_types_id" => $type))->order($order);

        $template->render();
    }

}
