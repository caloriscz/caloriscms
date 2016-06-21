<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = TRUE;

            $this->template->member = $this->database->table("users")->get($this->user->getId());

            if ($this->template->member->username != 'admin') {
                die('alone');
            }
        } else {
            die('alone');
        }
    }

    function renderdefault()
    {
        $this->template->channel = 'channel';
        $this->template->items = 'item';
    }

}
