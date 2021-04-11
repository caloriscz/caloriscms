<?php

namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class MenuControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($id = null, ?string $templateName = null): void
    {
        $presetId = null;
        $template = $this->getTemplate();
        $template->langPrefix = '';
        $template->langSuffix = '';

        $menus = $this->database->table('menu_menus')->get($id);

        if ($this->presenter->translator->getLocale() !== $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
            $template->langPrefix = '/' . $this->presenter->translator->getLocale();
        }

        $template->setFile(__DIR__ . '/' . $menus->type . 'Control.latte');

        $template->active = strtok($_SERVER['REQUEST_URI'], '?');

        $presetDbId = $this->database->table('menu')->where(['parent_id' => null, 'menu_menus_id' => $id]);

        if ($presetDbId->count() > 0) {
            $presetId = $presetDbId->fetch()->id;
        }

        $arr['parent_id'] = $presetId;
        $arr['menu_menus_id'] = $id;

        $template->id = $id;
        $template->class = $menus->class;
        $template->categories = $this->database->table('menu')->where($arr)->order('sorted ASC');

        $template->setFile(__DIR__ . '/' .  ($templateName ?? 'MenuControl') . '.latte');
        $template->render();
    }
}