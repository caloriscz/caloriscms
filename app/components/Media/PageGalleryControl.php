<?php

namespace Caloriscz\Media;

use Nette\Application\UI\Control;
use Nette\Database\Context;

/**
 *List
 * @package Caloriscz\Media
 */
class PageGalleryControl extends Control
{

    /** @var Context */
    public $database;
    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($page): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/PageGalleryControl.latte');

        $template->pages = $this->database->table('pictures')->where('pages.id = ? AND pages.public = 1', $page);

        $template->render();
    }

}
