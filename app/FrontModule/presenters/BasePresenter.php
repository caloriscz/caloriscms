<?php

namespace App\FrontModule\Presenters;

use Kdyby\Translation\Translator;
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

    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->addFilter(NULL, '\Filters::common');

        // Add mobile detect and its helper to template
        $template->_mobileDetect = $this->mobileDetect;
        $template->_deviceView = $this->deviceView;

        return $template;
    }

    protected function startup()
    {
        parent::startup();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->template->page = $this->database->table("pages")->get($this->getParameter("page_id"));

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
        $this->template->langDefault = $this->translator->getDefaultLocale();

        if ($this->translator->getLocale() != $this->translator->getDefaultLocale()) {
            $this->template->langSuffix = '_' . $this->translator->getLocale();
        }

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

    protected function createComponentPaging()
    {
        $control = new \PagingControl;
        return $control;
    }

    protected function createComponentNavigation()
    {
        $control = new \Caloriscz\Navigation\NavigationControl($this->database);
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

    protected function createComponentAdvancedSearch()
    {
        $control = new \Caloriscz\Page\Filters\AdvancedSearchControl($this->database);
        return $control;
    }

    protected function createComponentAlbum()
    {
        $control = new \AlbumControl($this->database);
        return $control;
    }

    protected function createComponentHead()
    {
        $control = new \Caloriscz\Navigation\Head\HeadControl($this->database);
        return $control;
    }

    protected function createComponentPageDocument()
    {
        $control = new \Caloriscz\Page\PageDocumentControl($this->database);
        return $control;
    }

    protected function createComponentAdminBar()
    {
        $control = new \Caloriscz\Menus\Admin\AdminBarControl($this->database);
        return $control;
    }

    protected function createComponentMenu()
    {
        $control = new \Caloriscz\Menus\MenuControl($this->database);
        return $control;
    }

    protected function createComponentFooter()
    {
        $control = new \Caloriscz\Navigation\Footer\FooterControl($this->database);
        return $control;
    }

    protected function createComponentMetaTags()
    {
        $control = new \Caloriscz\Navigation\Head\MetaTagsControl($this->database);
        return $control;
    }
}
