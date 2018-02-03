<?php

namespace Caloriscz\Page\Pages;

use App\Forms\Menu\InsertMenuControl;
use App\Model\Document;
use App\Model\Entity\Pages;
use App\Model\IO;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageListControl extends Control
{

    /** @var Context @inject  */
    public $database;

    /** @var EntityManager @inject */
    public $em;

    public $onSave;

    public function __construct(Context $database, EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;
    }

    protected function createComponentMenuInsert()
    {
        return new InsertMenuControl($this->database);
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

    /**
     * Change to public or private - private pages are not show to visitor
     */
    public function handlePublic()
    {
        $show = 1;
        $page = $this->database->table('pages')->get($this->getParameter('id'));

        if ($page->public === 1) {
            $show = 0;
        }

        $this->database->table('pages')->get($this->getParameter('id'))->update(['public' => $show]);

        $this->onSave($this->getParameter('type'));
    }

    public function render($type = 1, $fileTemplate = 'PageListControl')
    {
        $this->template->setFile(__DIR__ . '/' . $fileTemplate . '.latte');
        $order = 'FIELD(id, 1, 3, 4, 6, 2), title';

        if ($type === 2) {
            $order = 'title';
        }

        $pages = $this->em->getRepository(Pages::class);

        if ($fileTemplate === 'PageTreeControl') {
            $this->template->menu = $this->database->table('pages')->where('pages_id', null)->order($order);
        } else {
            $this->template->pages = $pages->findBy(['pagesTypes' => $type], ['title' => 'ASC']);
        }

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->type = $type;
        $this->template->database = $this->database;

        $this->template->render();
    }

}
