<?php

namespace Caloriscz\Media;

use App\Model\Category;
use App\Model\IO;
use Caloriscz\Utilities\PagingControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Paginator;

class ImageBrowserControl extends Control
{

    /**
     * @var Context
     */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;

    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl();
    }

    /**
     * Delete image
     * @param $id
     * @param $type
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id): void
    {
        $imageDb = $this->database->table('pictures')->get($id);

        IO::remove(APP_DIR . '/pictures/' . $imageDb->pages_id . '/' . $imageDb->name);
        IO::remove(APP_DIR . '/pictures/' . $imageDb->pages_id . '/tn/' . $imageDb->name);

        $imageDb->delete();

        $this->redirect('this', [
            'id' => $imageDb->pages_id,
            'type' => $this->getParameter('type'),
        ]);
    }

    /**
     * Set image as main  image
     */
    public function handleSetMain(): void
    {
        // Set all other media images in this folder as 0
        $this->database->table('pictures')->where(['pages_id' => $this->getParameter('id')])
            ->update(['main_file' => 0]);

        // Set chosen one as the main one
        $this->database->table('pictures')->get($this->getParameter('image'))->update(['main_file' => 1]);


        $this->presenter->redirect('this', ['id' => $this->getParameter('id'), 'image' => $this->getParameter('image')]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $mediaDb = $this->database->table('pictures')->where(['pages_id' => $this->presenter->getParameter('id')]);

        $paginator = new Paginator();
        $paginator->setItemCount($mediaDb->count('*'));
        $paginator->setItemsPerPage(16);
        $paginator->setPage($this->presenter->getParameter('page'));

        $template->args = $this->presenter->getParameters();
        $template->documents = $mediaDb->order('name');
        $template->paginator = $paginator;
        $template->productsArr = $mediaDb->limit($paginator->getLength(), $paginator->getOffset());

        if ($this->getParameter('id')) {
            $category = new Category($this->database);
            $template->breadcrumbs = $category->getPageBreadcrumb($this->presenter->getParameter('id'));
        } else {
            $template->breadcrumbs = [];
        }


        $template->setFile(__DIR__ . '/ImageBrowserControl.latte');
        $template->render();
    }
}
