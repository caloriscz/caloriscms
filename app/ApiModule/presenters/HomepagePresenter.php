<?php

namespace App\ApiModule\Presenters;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = true;

            $this->template->member = $this->database->table('users')->get($this->user->getId());

            if ($this->template->member->username !== 'admin') {
                die('alone');
            }
        } else {
            die('alone');
        }
    }

    public function renderdefault(): void
    {
        $this->template->channel = 'channel';
        $this->template->items = 'item';
    }

}
