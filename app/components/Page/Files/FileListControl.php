<?php
namespace Caloriscz\Page\File;

use Nette\Application\UI\Control;

class FileListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;
    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Delete file
     */
    function handleDeleteFile($id)
    {
        $this->database->table("media")->get($id)->delete();
        \App\Model\IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter("name"));
        $this->onSave($this->getParameter("name"));
    }

    public function render($page, $templateFile = false)
    {
        $template = $this->template;
        $template->page = $page->related('media', 'pages_id')->where('file_type = 0');
        $template->database = $this->database;

        if ($templateFile == true) {
            $template->setFile(__DIR__ . '/' . $templateFile . '.latte');

        } else {
            $template->setFile(__DIR__ . '/FileListControl.latte');
        }

        $template->render();
    }

}
