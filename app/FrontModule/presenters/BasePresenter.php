<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    use \IPub\MobileDetect\TMobileDetect;

    /** @var Nette\Database\Context */
    public $database;

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /** @var \BaseForm @inject */
    public $baseFormFactory;

    /** @var \Nette\Mail\IMailer @inject */
    public $mailer;

    public function __construct(\Nette\Database\Context $database, \Nette\Mail\IMailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    protected function startup()
    {
        parent::startup();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->template->settings = $this->database->table("settings")->fetchPairs("setkey", "setvalue");

        /* Maintenance mode */
        if ($this->template->settings["maintenance_enabled"]) {
            if (empty($this->template->settings["maintenance_message"])) {
                include_once('.maintenance.php');
            } else {
                echo $this->template->settings["maintenance_message"];
            }

            exit();
        }

        /* IP mode */
        $ip = explode(";", $this->template->settings["site_ip_whitelist"]);

        if (strlen($this->template->settings["site_ip_whitelist"]) < 4 || in_array($_SERVER['REMOTE_ADDR'], $ip)) {
        } else {
            if (empty($this->template->settings["maintenance_message"])) {
                include_once('.maintenance.php');
            } else {
                echo $this->template->settings["maintenance_message"];
            }

            exit();
        }

        /* Secret password mode */
        $secret = $_COOKIE["secretx"];

        if ($this->template->settings['site_cookie_whitelist'] != '') {
            if ($this->template->settings["site_cookie_whitelist"] != $secret) {
                if ($_GET["secretx"] == $this->template->settings["site_cookie_whitelist"]) {
                    setcookie("secretx", $this->template->settings["site_cookie_whitelist"], time() + 3600000);
                } else {
                    if (empty($this->template->settings["maintenance_message"])) {
                        include_once('.maintenance.php');
                    } else {
                        echo $this->template->settings["maintenance_message"];
                    }
                    exit();
                }
            } else {
                $message = "5";
            }
        }

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
            } else {
                $this->template->isLoggedIn = FALSE;
            }
        } catch (\Exception $e) {
            $this->template->isLoggedIn = FALSE;
        }

        $this->template->appDir = APP_DIR;
        $this->template->languageSelected = $this->translator->getLocale();

        if ($this->template->settings['store:enabled']) {
            $cart = new Model\Store\Cart($this->database, $this->user->getId(), $this->template->isLoggedIn);
            $cart->cleanCarts();

            $this->template->cartObject = $cart;
            $this->template->cartId = $cart->getCartId();
            $this->template->cartItems = $cart->getCartItems();
            $this->template->cartItemsArr = $cart->getItems();
            $this->template->cartTotal = $cart->getCartTotal($this->template->settings);
            $this->template->cartInfo = $cart->getCart();

            if ($cart->getItems()) {
                $this->template->cart = $cart->getItems()->order("id");
            }

            $bonus = new Model\Store\Bonus($this->database);
            $bonus->setUser($this->user->getId());
            $bonus->setCartTotal($this->template->cartTotal);
            $amountBonus = $bonus->getAmount($cart->getCartTotal($this->template->settings));
            $this->template->amountBonus = abs($amountBonus);

            if ($amountBonus <= 0) {
                $this->template->bonusEnabled = true;
            } else {
                $this->template->bonusEnabled = false;
            }
        }
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
            $nazvy = array(
                1 => $this->translator->translate('dictionary.days.Sunday'),
                2 => $this->translator->translate('dictionary.days.Monday'),
                3 => $this->translator->translate('dictionary.days.Tuesday'),
                4 => $this->translator->translate('dictionary.days.Wednesday'),
                5 => $this->translator->translate('dictionary.days.Thursday'),
                6 => $this->translator->translate('dictionary.days.Friday'),
                7 => $this->translator->translate('dictionary.days.Saturday'));

            return $nazvy[$s];
        });

        $template->addFilter('numericmonth', function ($s) {
            $nazvy = array(
                1 => $this->translator->translate('dictionary.months.January'),
                2 => $this->translator->translate('dictionary.months.February'),
                3 => $this->translator->translate('dictionary.months.March'),
                4 => $this->translator->translate('dictionary.months.April'),
                5 => $this->translator->translate('dictionary.months.May'),
                6 => $this->translator->translate('dictionary.months.June'),
                7 => $this->translator->translate('dictionary.months.July'),
                8 => $this->translator->translate('dictionary.months.August'),
                9 => $this->translator->translate('dictionary.months.September'),
                10 => $this->translator->translate('dictionary.months.October'),
                11 => $this->translator->translate('dictionary.months.November'),
                12 => $this->translator->translate('dictionary.months.December'),

            );

            return $nazvy[$s];
        });

        $template->addFilter('f', function ($s) {
            preg_match_all("/\[snippet\=\"([0-9]{1,10})\"\]/s", $s, $valsimp, PREG_SET_ORDER);

            if (count($valsimp) > 0) {
                for ($n = 0; $n < count($valsimp); $n++) {
                    $snippet = $this->database->table("snippets")->get($valsimp[$n][1]);

                    if ($snippet) {
                        $results = $snippet->content;
                    } else {
                        $results = null;
                    }

                    $s = str_replace($valsimp[$n][0], "$results", $s);
                }
            }

            preg_match_all("/\[file\=([0-9]{1,10})\]/s", $s, $valsimp, PREG_SET_ORDER);

            if (count($valsimp) > 0) {
                for ($n = 0; $n < count($valsimp); $n++) {
                    $snippet = $this->database->table("media")->get($valsimp[$n][1]);

                    if ($snippet) {
                        $results = '/media/' . $snippet->pages_id . '/' . $snippet->name;
                    } else {
                        $results = null;
                    }

                    $s = str_replace($valsimp[$n][0], "$results", $s);
                }
            }

            return $s;
        });


        $template->addFilter('toMins', function ($s) {
            if ($s < 60 && $s > 0) {
                $duration = '0:' . $s . '.';
            } elseif ($s >= 60) {
                $duration = ceil($s / 60) . ':' . ($s % 60) . '.';
            } else {
                $duration = '-';
            }

            return $duration;
        });

        // Add mobile detect and its helper to template
        $template->_mobileDetect = $this->mobileDetect;
        $template->_deviceView = $this->deviceView;

        return $template;
    }

    protected function createComponentPaging()
    {
        $control = new \PagingControl;
        return $control;
    }

    protected function createComponentSocialFacebook()
    {
        $control = new \Caloriscz\Social\FacebookControl;
        return $control;
    }

    protected function createComponentContact()
    {
        $control = new \ContactControl($this->database);
        return $control;
    }

    protected function createComponentSideMenu()
    {
        $control = new \Caloriscz\Menus\SideMenuControl($this->database);
        return $control;
    }

    protected function createComponentSideCat()
    {
        $control = new \Caloriscz\Menus\SideCatControl($this->database);
        return $control;
    }

    protected function createComponentNavbarMenu()
    {
        $control = new \Caloriscz\Menus\NavbarMenuControl($this->database);
        return $control;
    }

    protected function createComponentBlogPreview()
    {
        $control = new \BlogPreviewControl($this->database);
        return $control;
    }

    protected function createComponentEventsCalendar()
    {
        $control = new \EventsCalendarControl($this->database);
        return $control;
    }

    protected function createComponentSearch()
    {
        $control = new \Caloriscz\Product\SearchControl($this->database);
        return $control;
    }

    protected function createComponentAlbum()
    {
        $control = new \AlbumControl($this->database);
        return $control;
    }

    protected function createComponentHelpdesk()
    {
        $control = new \HelpdeskControl($this->database);
        return $control;
    }

    protected function createComponentCarouselBox()
    {
        $control = new \CarouselBoxControl($this->database);
        return $control;
    }

    protected function createComponentNewsletterForm()
    {
        $control = new \NewsletterFormControl($this->database);
        return $control;
    }

    protected function createComponentAdminBar()
    {
        $control = new \Caloriscz\Menus\AdminBarControl($this->database);
        return $control;
    }

    protected function createComponentMenu()
    {
        $control = new \Caloriscz\Menus\MenuControl($this->database);
        return $control;
    }

    /* Store components  ----------------------------------------------------------------------------------------------- */
    /* enable by uncomment this part
    protected function createComponentCartUpdater()
    {
        $control = new \Caloriscz\Cart\CartUpdaterControl($this->database);
        return $control;
    }

    protected function createComponentCartList()
    {
        $control = new \Caloriscz\Cart\ListControl($this->database);
        return $control;
    }

    protected function createComponentCartBox()
    {
        $control = new \Caloriscz\Cart\BoxControl($this->database);
        return $control;
    }
*/

}
