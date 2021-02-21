<?php

namespace App\ApiModule\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Database\Context;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function startup()
    {
        parent::startup();

        $memberDb = $this->database->table('users')->where('username', $this->getParameter('id'));

        if ($memberDb->count() > 0) {
            $this->template->memberDb = $memberDb->fetch();
        } else {
            $this->template->memberDb = false;
        }

        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');
    }

}
