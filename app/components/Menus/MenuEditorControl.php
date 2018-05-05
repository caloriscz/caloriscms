<?php

namespace Caloriscz\Menus;

use App\Forms\Menu\InsertMenuControl;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Tracy\Debugger;

class MenuEditorControl extends Control
{
    /** @var Context */
    public $database;

    /** @var EntityManager */
    public $em;

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
     * Delete category
     */
    public function handleDelete()
    {
        Debugger::barDump($this->getPresenter()->getParameter('node_id'));

        $this->database->table('menu')->get($this->getPresenter()->getParameter('node_id'))->delete();

        exit();
    }

    /**
     * Insert category
     */
    public function handleCreate()
    {
        $node = $this->database->table('menu')->insert([
            'title' => 'New node',
            'parent_id' => $this->getPresenter()->getParameter('node_id'),
            'sorted' => 1000000,
        ]);

        $nodeArr['id'] = $node->id;

        echo json_encode($nodeArr);
        exit();
    }

    /**
     * Rename category
     */
    public function handleRename()
    {
        Debugger::barDump($this->getPresenter()->getParameter('node_id'));

        $this->database->table('menu')->get($this->getPresenter()->getParameter('node_id'))->update([
            'title' => $this->getPresenter()->getParameter('text'),
        ]);

        exit();
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Throwable
     */
    public function handleSort()
    {
        $updateSorter = $this->em->getConnection()->prepare('SET @i = 1000;UPDATE `menu` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');
        $updateSorter->execute();
        $updateSorter->closeCursor();
        $arr['parent_id'] = null;
        $arr['sorted'] = null;

        $content = [];

        $idTo = $this->getPresenter()->getParameter('id_to');

        if ($idTo === '') {
            $idTo = null;
        }

        if ($this->getPresenter()->getParameter('id_from') !== $idTo) {
            $arr['parent_id'] = $idTo;
        }

        $menui = $this->database->table('menu')->where([
            'parent_id' => $idTo
        ])
            ->order('sorted')->limit(1, ($this->getPresenter()->getParameter('position')));

        if ($menui->count() > 0) {
            if ($this->getPresenter()->getParameter('position') == 0) {
                $arr['sorted'] = ($menui->fetch()->sorted - 1);
            } else {
                $arr['sorted'] = ($menui->fetch()->sorted + 1);
            }
        }

        $arr = array_filter($arr, 'strlen');

        $this->database->table('menu')->get($this->getPresenter()->getParameter('id_from'))->update($arr);
        exit();
    }

    public function render()
    {
        $template = $this->getTemplate();
        $categoryId = null;

        if ($this->presenter->getParameter('id')) {
            $categoryId = $this->presenter->getParameter('id');
        }

        $arr['parent_id'] = $categoryId;
        $arr['menu_menus_id'] = $this->presenter->getParameter('menu');

        $template->database = $this->database;
        $template->menu = $this->database->table('menu')->where($arr)->order('sorted');

        $template->setFile(__DIR__ . '/MenuEditorControl.latte');
        $template->render();
    }

}
