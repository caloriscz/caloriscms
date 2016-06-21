<?php

namespace Caloriscz\Media;

use Nette\Application\UI\Control;

class ImageBrowserControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;

    }

    /**
     * Delete image
     */
    function handleDelete($id)
    {
        $imageDb = $this->database->table("media")->get($this->getParameter("name"));
        $imageDb->delete();

        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $imageDb->name);
        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/tn/' . $imageDb->name);

        $this->redirect(this, array("id" => $this->presenter->getParameter("name"),));
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->catalogue = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $template->images = $this->database->table("media")
            ->where(array("pages_id" => $this->presenter->getParameter("id"), "file_type" => 1));

        $template->setFile(__DIR__ . '/ImageBrowserControl.latte');

        $template->render();
    }

}
