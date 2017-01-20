<?php
namespace Caloriscz\Media;

use Nette\Application\UI\Control;

class ImageListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentAlbum()
    {
        $control = new \AlbumControl($this->database);
        return $control;
    }

    protected function createComponentPaging()
    {
        $control = new \PagingControl;
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ImageListControl.latte');

        $cols = array(
            "pages_types_id" => 6,
            "pages_id" => $this->presenter->getParameter("page_id"),
        );

        $template->galleryId = $this->presenter->getParameter("id");
        $gallery = $this->database->table("pages")
            ->where($cols);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($gallery->count());
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->presenter->getParameter("page"));

        $template->paginator = $paginator;
        $template->galleries = $gallery->order("title")->limit($paginator->getLength(), $paginator->getOffset());
        $template->args = $this->getParameters(TRUE);

        $template->render();
    }

}
