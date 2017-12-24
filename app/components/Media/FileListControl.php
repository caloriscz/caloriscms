<?php
namespace Caloriscz\Media;

use Caloriscz\Page\PageSlugControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class FileListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentPageSlug()
    {
        $control = new PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/FileListControl.latte');
        $this->template->folders = $this->database->table("pages")->where(array("pages_id" => 6));
        $this->template->render();
    }

}
