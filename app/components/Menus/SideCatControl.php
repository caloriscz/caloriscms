<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;

class SideCatControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($id = null, $style = 'sidemenu', $level = 2)
    {
        $template = $this->template;

        $template->setFile(__DIR__ . '/SideCatControl.latte');

        $template->id = $id;
        $template->database = $this->database;
        $template->style = $style;
        $template->level = $level;
        $template->categories = $this->database->table('pages')->where(array(
            'pages_id' => $id,
            'pages_types_id' => 7,
        ))->order("sorted DESC");
        $template->render();
    }

}