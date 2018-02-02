<?php
namespace Caloriscz\Pages;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class ParamListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($page)
    {
        $template = $this->getTemplate();
        $template->page = $page;

        if ($this->presenter->translator->getLocale() !== $this->presenter->translator->getDefaultLocale()) {
            $template->langSuffix = '_' . $this->getPresenter()->translator->getLocale();
        }

        $template->setFile(__DIR__ . '/ParamListControl.latte');
        $template->render();
    }

}