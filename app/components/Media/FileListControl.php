<?php
namespace Caloriscz\Media;

use Nette\Application\UI\Control;

class FileListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentPageSlug()
    {
        $control = new \Caloriscz\Page\PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FileListControl.latte');

        $template->folders = $this->database->table("pages")->where(array("pages_id" => 6));

        $template->render();
    }

}
