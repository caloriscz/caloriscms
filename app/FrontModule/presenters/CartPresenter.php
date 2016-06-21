<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Cart presenter.
 */
class CartPresenter extends BasePresenter
{

    protected function createComponentBonusInfo()
    {
        $control = new \Caloriscz\Store\Bonus\BonusInfoControl($this->database);
        return $control;
    }

    function renderDefault()
    {
        $cart = new \App\Model\Store\Cart($this->database, $this->user->getId(), $this->template->isLoggedIn);

        $bonus = new \App\Model\Store\Bonus($this->database);
        $bonus->setUser($this->user->getId());
        $bonus->setCartTotal($this->template->cartTotal);

        if ($bonus->isEligibleForBonus($this->template->member->categories_id)) {
            $this->template->bonusEnabled = true;
        } else {
            $this->template->bonusEnabled = false;
        }
    }

}
