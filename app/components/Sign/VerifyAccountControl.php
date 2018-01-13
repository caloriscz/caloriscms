<?php
namespace Caloriscz\Sign;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class VerifyAccountControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function handleCheck()
    {
        $userLoggedDb = $this->database->table('users')->where(array(
            'activation' => $this->getPresenter()->getParameter('code'),
            'username' => $this->getPresenter()->getParameter('user')
        ));

        if ($userLoggedDb->count() === 0) {
            $this->getPresenter()->flashMessage('Aktivace není platná', 'error');
            $this->getPresenter()->redirect(':Front:Sign:verify');
        } else {
            $this->database->table('users')->where(array(
                'activation' => $this->getPresenter()->getParameter('code'),
                'username' => $this->getPresenter()->getParameter('user')
            ))->update(array(
                'state' => 1
            ));

            $this->getPresenter()->flashMessage('Úspěšně ověřeno. Nyní se můžete přihlásit.', 'note');
            $this->getPresenter()->redirect(':Front:Sign:in');
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/VerifyAccountControl.latte');

        $template->render();
    }

}