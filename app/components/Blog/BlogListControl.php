<?php
namespace Caloriscz\Blog;

use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Utils\Paginator;

class BlogListControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;

        $blog = $this->database->table('pages')->where([
            'date_published <= ?' => date('Y-m-d H:i:s'),
            'pages_types_id' => 2,
        ])->order('date_created DESC');

        $paginator = new Paginator();
        $paginator->setItemCount($blog->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter('page') ?? 1);

        $template->blog = $blog->limit($paginator->getLength(), $paginator->getOffset());
        $template->paginator = $paginator;
        $template->args = $this->getParameters();
        $template->setFile(__DIR__ . '/BlogListControl.latte');
        $template->render();
    }
}
