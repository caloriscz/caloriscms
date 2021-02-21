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

        $blog = $this->database->table('pages')
            ->where($arr)
            ->limit($limit)
            ->order('date_published DESC');
        $template->settings = $this->presenter->template->settings;
        $template->blog = $blog;

        $template->render();
    }

}
