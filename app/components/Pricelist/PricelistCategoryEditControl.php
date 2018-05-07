<?php

namespace App\Forms\Pricelist;

use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Tracy\Debugger;

class PricelistCategoryEditControl extends Control
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


    /**
     * Delete category
     */
    public function handleDelete()
    {
        $this->database->table('pricelist_categories')->get($this->getPresenter()->getParameter('node_id'))->delete();
        exit();
    }

    /**
     * Insert category
     */
    public function handleCreate()
    {
        $node = $this->database->table('pricelist_categories')->insert([
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

        $this->database->table('pricelist_categories')->get($this->getPresenter()->getParameter('node_id'))->update([
            'title' => $this->getPresenter()->getParameter('text'),
            'pricelist_lists_id' => $this->getPresenter()->getParameter('pricelist'),
        ]);

        exit();
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Throwable
     */
    public function handleSort()
    {
        $updateSorter = $this->em->getConnection()->prepare('SET @i = 1000;UPDATE `pricelist_categories` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');
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

        $menui = $this->database->table('pricelist_categories')->where([
            'parent_id' => $idTo
        ])
            ->order('sorted')->limit(1, $this->getPresenter()->getParameter('position'));

        if ($menui->count() > 0) {
            if ($this->getPresenter()->getParameter('position') === 0) {
                $arr['sorted'] = ($menui->fetch()->sorted - 1);
            } else {
                $arr['sorted'] = ($menui->fetch()->sorted + 1);
            }
        }

        $arr = array_filter($arr, '\strlen');

        $this->database->table('pricelist_categories')->get($this->getPresenter()->getParameter('id_from'))->update($arr);
        exit();
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $categoryId = null;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/PricelistCategoryEditControl.latte');

        if ($this->presenter->getParameter('id')) {
            $categoryId = $this->presenter->getParameter('id');
        }

        $arr['parent_id'] = $categoryId;
        $arr['pricelist_lists_id'] = $this->presenter->getParameter('pricelist');

        $template->database = $this->database;
        $template->menuList = $this->database->table('pricelist_lists')->get($this->presenter->getParameter('pricelist'));
        $template->menu = $this->database->table('pricelist_categories')->where($arr)->order('sorted');
        $template->render();
    }

}
