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
    public function render($id, $type = null)
    {
        $template = $this->template;
        $template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams["page"]);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/SettingsCategoriesControl.latte');

        $template->id = $id;
        $template->idActive = $this->presenter->getParameter("id");
        $template->menu = $this->database->table('settings_categories')->where('parent_id', $id);
        $template->render();
    }

}