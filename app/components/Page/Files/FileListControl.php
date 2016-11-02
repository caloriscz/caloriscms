<?php
namespace Caloriscz\Page\File;

use Nette\Application\UI\Control;

class FileListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($page)
    {
        $template = $this->template;
        $template->page = $page->related('media', 'pages_id')->where('file_type = 0');
        $template->database = $this->database;
        $template->setFile(__DIR__ . '/FileListControl.latte');

        $template->render();
    }

}
