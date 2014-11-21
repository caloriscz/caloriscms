<?php

namespace App\AdminModule\Presenters;

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

}
