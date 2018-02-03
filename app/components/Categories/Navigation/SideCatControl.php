<?php
namespace Caloriscz\Categories\Navigation;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class SideCatControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($style = 'sidemenu', $templateFile = 'SideCatControl', $id = null)
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/' . $templateFile . '.latte');
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