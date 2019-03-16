<?php

namespace Caloriscz\Social;

use Nette\Application\UI\Control;

class FacebookControl extends Control {

    public function render($type = 'page'): void
    {
        $this->template->setFile(__DIR__ . '/FacebookControl.latte');
        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->type = $type;
        $this->template->render();
    }
}
