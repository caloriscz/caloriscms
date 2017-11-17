<?php

namespace Caloriscz\Page\Pages;

use App\Model\Document;
use App\Model\Entity\Pages;
use App\Model\IO;
use Caloriscz\Menus\MenuForms\InsertMenuControl;
use Nette\Application\UI\Control;

class PageListControl extends Control
{

    /** @var \Nette\Database\Context @inject  */
    public $database;

    /** @var \Kdyby\Doctrine\EntityManager @inject */
    public $em;

    public $onSave;

    public function __construct(\Nette\Database\Context $database, \Kdyby\Doctrine\EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;
    }

    protected function createComponentMenuInsert()
    {
        $control = new InsertMenuControl($this->database);
        return $control;
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

        $this->database->table('pages')->get($this->getParameter('id'))->update(array('public' => $show));

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

        $this->template->type = $type;
        $this->template->database = $this->database;

        $this->template->render();
    }

}
