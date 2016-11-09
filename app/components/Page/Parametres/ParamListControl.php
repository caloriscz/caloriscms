<?php
namespace Caloriscz\Page\Parametres;

use Nette\Application\UI\Control;

class ParamListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($page)
    {
        $template = $this->template;
        $template->page = $page;

        if ($this->presenter->translator->getLocale() != $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->presenter->translator->getLocale();
        }

        $template->setFile(__DIR__ . '/ParamListControl.latte');

        $template->render();
    }

}