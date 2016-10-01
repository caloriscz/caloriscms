<?php
namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class VerifyAccountControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function handleCheck()
    {
        $userLoggedDb = $this->database->table('users')->where(array(
            'activation' => $this->presenter->getParameter("code"),
            'username' => $this->presenter->getParameter("user")
        ));

        if ($userLoggedDb->count() == 0) {
            $this->presenter->flashMessage('Aktivace není platná', 'error');
            $this->presenter->redirect(":Front:Sign:verify");
        } else {
            $this->database->table("users")->where(array(
                "activation" => $this->presenter->getParameter("code"),
                'username' => $this->presenter->getParameter("user")
            ))->update(array(
                "state" => 1
            ));

            $this->presenter->flashMessage('Úspěšně ověřeno. Nyní se můžete přihlásit.', 'note');
            $this->presenter->redirect(":Front:Sign:in");
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/VerifyAccountControl.latte');

        $template->render();
    }

}