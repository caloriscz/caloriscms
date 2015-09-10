<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var Nette\Database\Context */
    public $database;

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function startup()
    {
        parent::startup();

        // Arguments for language switch box
        $parametres = $this->getParameters(TRUE);
        unset($parametres["locale"]);
        $this->template->args = $parametres;

        $this->template->langSelected = $this->translator->getLocale();

        try {
            if ($this->user->isLoggedIn()) {
                $this->template->isLoggedIn = TRUE;

                $this->template->member = $this->database->table("users")
                        ->get($this->user->getId());
            }
        } catch (\Exception $e) {
            
        }

        if ($this->user->isLoggedIn()) {
            $cartDb = $this->database->table("cart")->where(array("users_id" => $this->user->getId()));

            if ($cartDb->count() == 0) {
                $cartGuestDb = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));

                if ($cartGuestDb->count() > 0) {
                    $cartItems = $cartGuestDb->related("cart_items", "cart_id")->sum("amount");
                    $cartTotal = $cartGuestDb->related("cart_items", "cart_id")->sum("price");
                } else {
                    $cartItems = 0;
                    $cartTotal = 0;
                }
            } else {
                $cartCount = $cartDb->fetch()->id;

                $cartItems = $this->database->table("cart_items")->where(array("cart_id" => $cartCount))->sum("amount");
                $cartTotal = $this->database->table("cart_items")->where(array("cart_id" => $cartCount))->sum("price");
            }
        } else {
            $cartDb = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));

            if ($cartDb->count() > 0) {
                $cartStats = $cartDb->fetch();
                $cartItems = $cartStats->related("cart_items", "cart_id")->sum("amount");
                $cartTotal = $cartStats->related("cart_items", "cart_id")->sum("price");
            } else {
                $cartItems = 0;
                $cartTotal = 0;
            }
        }

        // Set values from db
        $this->template->settings = $this->database->table("settings")->fetchPairs("setkey", "setvalue");

        $this->template->cartItems = $cartItems;
        $this->template->cartTotal = $cartTotal;

        $this->template->appDir = APP_DIR;
        $this->template->languageSelected = $this->translator->getLocale();
    }

    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->addFilter('ago', function ($s, $add = 0) {
            $date = new \DateTime();
            $date->setDate(date('Y', strtotime($s)), date('m', strtotime($s)), date('d', strtotime($s)));
            $interval = $date->diff(new \DateTime('now'));
            $daysAgo = $interval->format('%a days');

            return $daysAgo;
        });
        $x = $this->translator->getLocale();
        $template->addFilter('numericday', function ($s) {
            $nazvy = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');

            return $nazvy[$s];
        });

        $template->addFilter('toMins', function ($s) {
            if ($s < 60 && $a > 0) {
                $duration = '0:' . $s . '.';
            } elseif ($s >= 60) {
                $duration = ceil($s / 60) . ':' . ($s % 60) . '.';
            } else {
                $duration = '-';
            }

            return $duration;
        });

        return $template;
    }

}
