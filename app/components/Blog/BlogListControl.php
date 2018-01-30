<?php
namespace Caloriscz\Blog;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Paginator;

class BlogListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentPaging()
    {
        $control = new PagingControl();
        return $control;
    }

    public function render()
    {
        $template = $this->template;

        $blog = $this->database->table('pages')->where(array(
            'date_published <= ?' => date('Y-m-d H:i:s'),
            'pages_types_id' => 2,
        ))
            ->order('date_created DESC');

        $paginator = new Paginator();
        $paginator->setItemCount($blog->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter("page"));

        $template->blog = $blog->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->getParameters();

        $template->setFile(__DIR__ . '/BlogListControl.latte');

        $template->render();
    }

}
