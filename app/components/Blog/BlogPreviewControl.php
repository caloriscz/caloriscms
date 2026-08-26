<?php
namespace Caloriscz\Blog;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class BlogPreviewControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($limit = 3): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/BlogPreviewControl.latte');

        $arr['pages_types_id'] = 2;
        $arr['public'] = 1;

        $arr['date_published <= ?'] = date('Y-m-d H:i:s');

        $blogPageType = $this->database->table('pages_types')->get(2);
        $blogRootId = $blogPageType && $blogPageType->pages_id !== null ? (int) $blogPageType->pages_id : null;

        if ($blogRootId !== null) {
            $arr['pages_id'] = $blogRootId;
        }

        $blog = $this->database->table('pages')
            ->where($arr)
            ->limit($limit)
            ->order('date_published DESC');
        $template->settings = $this->presenter->template->settings;
        $template->blog = $blog;

        $template->render();
    }

}
