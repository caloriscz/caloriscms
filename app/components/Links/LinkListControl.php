<?php

namespace Caloriscz\Links;

use Nette\Application\UI\Control;

class LinkListControl extends Control
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

        $template->links = $this->database->table("links")->where("categories_id", $id);

        $template->setFile(__DIR__ . '/LinkGalleryControl.latte');

        $template->render();
    }

}
