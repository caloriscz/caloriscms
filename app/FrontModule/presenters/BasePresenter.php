<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database) {
        $this->database = $database;
    }

    protected function startup() {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = TRUE;

            $this->template->member = $this->database->table("users")->get($this->user->getId());
        }

        if ($this->isAjax()) {
            $this->template->style = FALSE;
        } else {
            $this->template->style = TRUE;
        }
    }

}
