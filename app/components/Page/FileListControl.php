<?php
namespace Caloriscz\Page;

use App\Model\IO;
use App\Security\CsrfProtectedMutation;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class FileListControl extends Control
{
    use CsrfProtectedMutation;
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
        $this->requireCsrfToken();

        $pageId = (new \App\Model\MediaStorage($this->database, APP_DIR))->deleteFile('media', (int) $id)
            ?? $this->getParameter('name');

        $this->onSave($pageId);
    }

    public function render($page, $templateFile = false): void
    {
        $template = $this->getTemplate();
        $template->page = $page->related('media', 'pages_id');
        $template->database = $this->database;
        $template->csrfToken = $this->getCsrfToken();

        if ($templateFile == true) {
            $template->setFile(__DIR__ . '/' . $templateFile . '.latte');

        } else {
            $template->setFile(__DIR__ . '/FileListControl.latte');
        }

        $template->render();
    }
}
