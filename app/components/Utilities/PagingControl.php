<?php

namespace Caloriscz\Utilities;

use Nette\Application\UI\Control;

class PagingControl extends Control
{

    /**
     * @param $args
     * @param $paginator
     */
    public function render($args, $paginator): void
    {
        $this->template->setFile(__DIR__ . '/PagingControl.latte');
        $this->template->args = $args;
        $this->template->paginator = $paginator;
        $this->template->render();
    }

}
