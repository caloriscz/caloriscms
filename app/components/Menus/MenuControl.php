<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;

class MenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($id = null, $style = 'sidemenu', $controlName = null)
    {
        $template = $this->template;

        if ($controlName == null) {
            $controlName = 'MenuControl';
        }

        if ($this->presenter->translator->getLocale() != $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
            $template->langPrefix = '/' . $this->presenter->translator->getLocale();
        }

        $template->setFile(__DIR__ . '/' . $controlName . '.latte');

        $template->active = strtok($_SERVER["REQUEST_URI"], '?');

        $template->id = $id;
        $template->style = $style;
        $template->categories = $this->database->table('menu')->where('parent_id', $id)->order('sorted DESC');
        $template->render();
    }

}
