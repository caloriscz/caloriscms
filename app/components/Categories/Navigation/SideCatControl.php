<?php
namespace Caloriscz\Categories\Navigation;

use Nette\Application\UI\Control;

class SideCatControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($style = 'sidemenu', $templateFile = 'SideCatControl', $id = null)
    {
        $template = $this->template;

        $template->setFile(__DIR__ . '/' . $templateFile . '.latte');

        $template->id = $id;
        $template->database = $this->database;
        $template->style = $style;
        $template->level = 2;
        $template->categories = $this->database->table('pages')->where(array(
            'pages_id' => $id,
            'pages_types_id' => 7,
        ))->order('sorted DESC');
        $template->render();
    }

}