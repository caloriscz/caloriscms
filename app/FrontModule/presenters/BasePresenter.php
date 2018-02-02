<?php
namespace App\FrontModule\Presenters;

use App\Forms\Pages\AdvancedSearchControl;
use Caloriscz\Menus\Admin\AdminBarControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\SideMenuControl;
use Caloriscz\Navigation\Footer\FooterControl;
use Caloriscz\Navigation\Head\HeadControl;
use Caloriscz\Navigation\NavigationControl;
use Caloriscz\Page\PageDocumentControl;
use Caloriscz\Page\PageSlugControl;
use Caloriscz\Page\PageTitleControl;
use Caloriscz\Utilities\PagingControl;
use Nette;

/**
 * Base presenter for all application presenters.
 */
class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    public $database;

    /** @var \Kdyby\Doctrine\EntityManager */
    public $em;

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /** @var \Nette\Mail\IMailer @inject */
    public $mailer;

    /** @var Nette\Http\IRequest @inject */
    public $request;

    public function __construct(Nette\Database\Context $database, \Nette\Mail\IMailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->addFilter(NULL, '\Filters::common');

        return $template;
    }

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table('pages')->get($this->getParameter('page_id'));
        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');

        // Maintenance mode
        if ($this->template->settings['maintenance_enabled']) {
            if (empty($this->template->settings['maintenance_message'])) {
                include_once('.maintenance.php');
            } else {
                echo $this->template->settings['maintenance_message'];
            }

            exit();
        }

        // IP mode
        $ip = explode(';', $this->template->settings['site_ip_whitelist']);

        if (strlen($this->template->settings['site_ip_whitelist']) < 4 || in_array($_SERVER['REMOTE_ADDR'], $ip, true)) {
        } else {
            if (empty($this->template->settings['maintenance_message'])) {
                include_once('.maintenance.php');
            } else {
                echo $this->template->settings['maintenance_message'];
            }

            exit();
        }

        /* Secret password mode */
        $secret = $this->request->getCookie('secretx');

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
        $this->template->slugArray = [];


    }

    protected function createComponentPaging()
    {
        return new PagingControl;
    }

    protected function createComponentNavigation()
    {
        return new NavigationControl($this->database);
    }

    protected function createComponentContact()
    {
        return new \ContactControl($this->database);
    }

    protected function createComponentSideMenu()
    {
        return new SideMenuControl($this->database);
    }

    protected function createComponentAdvancedSearch()
    {
        return new AdvancedSearchControl($this->database);
    }

    protected function createComponentHead()
    {
        return new HeadControl($this->database);
    }

    protected function createComponentPageTitle()
    {
        return new PageTitleControl($this->database);
    }

    protected function createComponentPageDocument()
    {
        return new PageDocumentControl($this->database);
    }

    protected function createComponentPageSlug()
    {
        return new PageSlugControl($this->database);
    }

    protected function createComponentAdminBar()
    {
        return new AdminBarControl($this->database);
    }

    protected function createComponentMenu()
    {
        return new MenuControl($this->database);
    }

    protected function createComponentFooter()
    {
        return new FooterControl($this->database);
    }

    /**
     * Content editable snippets
     */
    public function handleSnippet()
    {
        $this->database->table('snippets')->get($this->getParameter('snippetId'))->update(array(
            'content' => $this->getParameter('text')
        ));
        exit();

    }

    /**
     * Content editable page title
     */
    public function handlePagetitle()
    {
        $this->database->table('pages')->where('id', $this->getParameter('editorId'))->update(array(
            'title' => $this->getParameter('text')
        ));
        exit();

    }
}
