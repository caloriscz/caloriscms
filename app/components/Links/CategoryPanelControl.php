<?php

namespace Caloriscz\Links;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class CategoryPanelControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Delete category
     */
    public function handleDelete(): void
    {
        $this->database->table('links_categories')->get($this->getPresenter()->getParameter('node_id'))->delete();
        exit();
    }

    /**
     * Insert category
     */
    public function handleCreate(): void
    {
        $node = $this->database->table('links_categories')->insert([
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
    public function handleRename(): void
    {
        $this->database->table('links_categories')->get($this->getPresenter()->getParameter('node_id'))->update([
            'title' => $this->getPresenter()->getParameter('text')
        ]);

        exit();
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Throwable
     */
    public function handleSort(): void
    {
        $this->database->query('SET @i = 1000;UPDATE `links_categories` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');
        $arr['parent_id'] = null;
        $arr['sorted'] = null;

        $idTo = $this->getPresenter()->getParameter('id_to');

        if ($idTo === '') {
            $idTo = null;
        }

        if ($this->getPresenter()->getParameter('id_from') !== $idTo) {
            $arr['parent_id'] = $idTo;
        }

        $menui = $this->database->table('links_categories')->where([
            'parent_id' => $idTo
        ])->order('sorted')->limit(1, $this->getPresenter()->getParameter('position'));

        if ($menui->count() > 0) {
            if ($this->getPresenter()->getParameter('position') === 0) {
                $arr['sorted'] = ($menui->fetch()->sorted - 1);
            } else {
                $arr['sorted'] = ($menui->fetch()->sorted + 1);
            }
        }

        $arr = array_filter($arr, '\strlen');

        $this->database->table('links_categories')->get($this->getPresenter()->getParameter('id_from'))->update($arr);
        exit();
    }

    /**
     * @param null $id
     * @param null $type
     */
    public function render($id = null, $type = null): void
    {
        $template = $this->getTemplate();
        $template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/CategoryPanelControl.latte');

        $template->id = $id;
        $template->database = $this->database;
        $template->idActive = $this->presenter->getParameter('id');

        $arr['parent_id'] = null;
        $template->menu = $this->database->table('links_categories')->where($arr);


        $template->render();
    }

}
