<?php

namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class NavbarMenuControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($id, $style = 'navbar'): void
    {
        $template = $this->getTemplate();
        $template->appDir = APP_DIR;
        $template->setFile(__DIR__ . '/NavbarMenuControl.latte');

        $template->id = $id;
        $template->style = $style;

        // Language support > 1 = multilanguage else only one langauge
        $template->languages = $this->database->table('languages')->where('default = 1 AND code = ?', $this->presenter->translator->getLocale());

        if ($template->languages->count() === 0) {
            $template->needsPrefix = true;
        } else {
            $template->needsPrefix = false;
        }

        $template->langSelected = $this->presenter->translator->getLocale();

        // load menu info from menu_menus
        $template->menu = $this->database->table('menu_menus')->get($id);

        if ($template->menu) {
            $template->class = $template->menu->class;
            $template->categories = $this->database->table('menu')->where( ['parent_id' => $id]);
        }

        $template->render();
    }

}
