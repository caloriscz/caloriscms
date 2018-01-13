<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;

class SettingsCategoriesControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * 
     * @param type $id
     * @param type $type Specifies type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render($id = null, $type = null)
    {
        $this->template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $this->template->args = $getParams;

        $this->template->setFile(__DIR__ . '/SettingsCategoriesControl.latte');

        $this->template->id = $id;
        $this->template->idActive = $this->presenter->getParameter('id');
        $this->template->menu = $this->database->table('settings_categories')->where('parent_id', null);
        $this->template->render();
    }

}