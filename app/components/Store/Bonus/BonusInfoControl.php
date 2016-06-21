<?php

namespace Caloriscz\Store\Bonus;

use Nette\Application\UI\Control;

class BonusInfoControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($member)
    {
        $template = $this->template;
        $template->isLoggedIn = $this->presenter->template->isLoggedIn;

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user->getId(), $this->presenter->template->isLoggedIn);

        if ($this->presenter->user->isLoggedIn()) {
            $bonus = new \App\Model\Store\Bonus($this->database);
            $bonus->setUser($this->presenter->user->getId());
            $bonus->setCartTotal($this->presenter->template->cartTotal);
            $amountBonus = $bonus->getAmount($cart->getCartTotal($this->presenter->template->settings));
            $template->amountBonus = abs($amountBonus);

            if ($bonus->isEligibleForBonus($member->categories_id)) {
                $template->bonusEnabled = true;
            } else {
                $template->bonusEnabled = false;
            }
        }

        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/BonusInfoControl.latte');

        $template->render();
    }

}
