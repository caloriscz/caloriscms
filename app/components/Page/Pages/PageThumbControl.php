<?php

namespace Caloriscz\Page\Pages;

use App\Model\Document;
use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageThumbControl extends Control
{

    /** @var Context */
    public $database;
    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Delete page
     */
    public function handleDelete($id)
    {
        $doc = new Document($this->database);
        $doc->delete($id);
        IO::removeDirectory(APP_DIR . '/media/' . $id);

        $this->onSave($this->getParameter('type'));
    }

    public function handlePublic()
    {
        $page = $this->database->table('pages')->get($this->getParameter('id'));

        if ($page->public === 1) {
            $show = 0;
        } else {
            $show = 1;
        }

        $this->database->table('pages')->get($this->getParameter('id'))->update(array('public' => $show));

        $this->onSave($this->getParameter('type'));
    }

    public function render($type, $id = '')
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/PageThumbControl.latte');

        if ($type === 6) {
            $pagesId = 4;
        } else {
            $pagesId = 6;
        }

        if ($id === '') {
            $arr = [
                'pages_types_id' => $type,
                'pages_id' => $pagesId,
            ];
        } else {
            $arr = [
                'pages_types_id' => $type,
                'pages_id' => $id,
            ];
        }

        $template->pages = $this->database->table('pages')->where($arr);
        $template->render();
    }

}
