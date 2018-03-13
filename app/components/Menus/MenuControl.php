<?php

namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class MenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param null $id
     */
    public function render($id = null)
    {
        $template = $this->getTemplate();
        $template->langSuffix = '';
        $template->langPrefix = '';

        $menus = $this->database->table('menu_menus')->get($id);

        if ($this->presenter->translator->getLocale() !== $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
            $template->langPrefix = '/' . $this->presenter->translator->getLocale();
        }

        $template->setFile(__DIR__ . '/MenuControl.latte');

        $template->active = strtok($_SERVER['REQUEST_URI'], '?');

        $arr['parent_id'] = null;
        $arr['menu_menus_id'] = $id;

        $template->id = $id;
        $template->class = $menus->class;
        $template->categories = $this->database->table('menu')->where($arr)->order('sorted DESC');
        $template->render();
    }

}
