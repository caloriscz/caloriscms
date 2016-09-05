<?php

namespace Caloriscz\Media;

use Nette\Application\UI\Control;

class FileListControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;

    }

    public function render($id)
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->files = $this->database->table("media")
            ->where(array("pages_id" => $id));

        $template->setFile(__DIR__ . '/FileListControl.latte');

        $template->render();
    }

}
