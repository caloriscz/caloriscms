<?php

namespace Caloriscz\Utilities;

use Nette\Application\UI\Control;

class PagingControl extends Control
{

    public function render($args, $paginator)
    {
        $this->template->setFile(__DIR__ . '/PagingControl.latte');
        $this->template->args = $args;
        $this->template->paginator = $paginator;
        $this->template->render();
    }

}
