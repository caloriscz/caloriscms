<?php
namespace Caloriscz\Page;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class FileListControl extends Control
{
    public Explorer $database;
    public $onSave;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Delete file
     * @param $id
     */
    public function handleDeleteFile($id): void
    {
        $this->database->table('media')->get($id)->delete();
        IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter('name'));
        $this->onSave($this->getParameter('name'));
    }

    public function render($page, $templateFile = false): void
    {
        $template = $this->getTemplate();
        $template->page = $page->related('media', 'pages_id');
        $template->database = $this->database;

        if ($templateFile == true) {
            $template->setFile(__DIR__ . '/' . $templateFile . '.latte');

        } else {
            $template->setFile(__DIR__ . '/FileListControl.latte');
        }

        $template->render();
    }
}