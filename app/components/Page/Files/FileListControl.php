<?php
namespace Caloriscz\Page\File;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class FileListControl extends Control
{

    /** @var Context */
    public $database;
    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Delete file
     * @param $id
     */
    public function handleDeleteFile($id)
    {
        $this->database->table('media')->get($id)->delete();
        IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter('name'));
        $this->onSave($this->getParameter('name'));
    }

    public function render($page, $templateFile = false)
    {
        $template = $this->getTemplate();
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
