<?php
use Nette\Application\UI\Control;

class PagingControl extends Control
{

    public function render($args, $paginator)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/PagingControl.latte');
        $template->args = $args;
        $template->paginator = $paginator;
        
        $template->render();
    }

}
