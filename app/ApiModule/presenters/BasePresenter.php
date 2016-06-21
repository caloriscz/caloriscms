<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function startup()
    {
        parent::startup();

        $memberDb = $this->database->table("users")->where("username", $this->getParameter("id"));

        if ($memberDb->count() > 0) {
            $this->template->memberDb = $memberDb->fetch();
        } else {
            $this->template->memberDb = FALSE;
        }

        $this->template->settings = $this->database->table("settings")->fetchPairs("setkey", "setvalue");
    }

}
