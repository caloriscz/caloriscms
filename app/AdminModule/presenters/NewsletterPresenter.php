<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class NewsletterPresenter extends BasePresenter
{
    /**
     * Newsletter delete
     */
    function handleDelete($id)
    {
        $this->database->table("newsletter")->get($id)->delete();

        $this->redirect(":Admin:Newsletter:default", array("id" => null));
    }

    public function renderDefault()
    {
        $this->template->newsletter = $this->database->table("newsletter")->order("email");
    }

}
