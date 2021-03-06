<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class SideCatControl extends Control
{

   public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($style = 'sidemenu', $templateFile = 'SideCatControl', $id = null): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . 'SideCatControl.php/' . $templateFile . '.latte');
        $template->id = $id;
        $template->database = $this->database;
        $template->style = $style;
        $template->level = 2;
        $template->categories = $this->database->table('pages')->where([
            'pages_id' => $id,
            'pages_types_id' => 7,
        ])->order('sorted DESC');
        $template->render();
    }

}