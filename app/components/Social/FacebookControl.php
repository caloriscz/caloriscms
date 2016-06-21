<?php

namespace Caloriscz\Social;

use Nette\Application\UI\Control;

class FacebookControl extends Control {

    public function render($type = 'page') {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FacebookControl.latte');
        $template->settings = $this->presenter->template->settings;
        $template->type = $type;

        $template->render();
    }

}
