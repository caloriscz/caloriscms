<?php

namespace Caloriscz\Links;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class LinkListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;

    }

    public function render($id)
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->links = $this->database->table('links')->where('categories_id', $id);
        $template->setFile(__DIR__ . '/LinkGalleryControl.latte');

        $template->render();
    }

}
