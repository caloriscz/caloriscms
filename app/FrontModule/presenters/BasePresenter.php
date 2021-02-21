<?php

namespace App\FrontModule\Presenters;

use App\Forms\Pages\AdvancedSearchControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Navigation\AdminBarControl;
use Caloriscz\Navigation\FooterControl;
use Caloriscz\Navigation\HeadControl;
use Caloriscz\Navigation\NavigationControl;
use Caloriscz\Page\PageDocumentControl;
use Caloriscz\Page\PageSlugControl;
use Caloriscz\Page\PageTitleControl;
use Caloriscz\Utilities\PagingControl;
use Symfony\Component\Translation\Translator;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\Http\IRequest;
use Nette\Mail\IMailer;


/**
 * Base presenter for all application presenters.
 * @package App\FrontModule\Presenters
 * @property-read \Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 */
abstract class BasePresenter extends Presenter
{
    public Explorer $database;

    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    /** @var IMailer @inject */
    public $mailer;

    /** @var IRequest @inject */
    public $request;

    public function __construct(Explorer $database, IMailer $mailer)
    {
        parent::__construct();
        $this->database = $database;
        $this->mailer = $mailer;
    }

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table('pages')->get($this->getParameter('page_id'));
        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');

        // Maintenance mode
        if ($this->template->settings['maintenance_enabled']) {
            if (empty($this->template->settings['maintenance_message'])) {
                include_once '.maintenance.php';
            } else {
                echo $this->template->settings['maintenance_message'];
            }

            exit();
        }

        // IP mode
        $ip = explode(';', $this->template->settings['site_ip_whitelist']);

        if (strlen($this->template->settings['site_ip_whitelist']) >= 4 && !in_array($_SERVER['REMOTE_ADDR'], $ip, true)) {
            if (empty($this->template->settings['maintenance_message'])) {
                include_once '.maintenance.php';
            } else {
                echo $this->template->settings['maintenance_message'];
            }

            exit();
        }

        // Secret password mode
        $secret = $this->request->getCookie('secretx');

        if ($this->template->settings['site_cookie_whitelist'] !== '') {
            if ($this->template->settings['site_cookie_whitelist'] !== $secret) {
                if ($_GET['secretx'] === $this->template->settings['site_cookie_whitelist']) {
                    setcookie('secretx', $this->template->settings['site_cookie_whitelist'], time() + 3600000);
                } else {
                    if (empty($this->template->settings['maintenance_message'])) {
                        include_once('.maintenance.php');
                    } else {
                        echo $this->template->settings['maintenance_message'];
                    }
                    exit();
                }
            }
        }

        // Arguments for language switch box
        $parametres = $this->getParameters(true);
        unset($parametres['locale']);
        $this->template->args = $parametres;

        $this->template->langSelected = $this->translator->getLocale();
        $this->template->langDefault = $this->translator->getDefaultLocale();

        if ($this->translator->getLocale() !== $this->translator->getDefaultLocale()) {
            $this->template->langSuffix = '_' . $this->translator->getLocale();
        }

        try {
            if ($this->user->isLoggedIn()) {
                $this->template->isLoggedIn = true;

                $this->template->member = $this->database->table('users')->get($this->user->getId());
            } else {
                $this->template->isLoggedIn = false;
            }
        } catch (\Exception $e) {
            $this->template->isLoggedIn = false;
        }

        $this->template->appDir = APP_DIR;
        $this->template->languageSelected = $this->translator->getLocale();
        $this->template->slugArray = [];
    }

    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl;
    }

    protected function createComponentNavigation(): NavigationControl
    {
        return new NavigationControl($this->database);
    }

    protected function createComponentAdvancedSearch(): AdvancedSearchControl
    {
        return new AdvancedSearchControl($this->database);
    }

    protected function createComponentHead(): HeadControl
    {
        return new HeadControl($this->database);
    }

    protected function createComponentPageTitle(): PageTitleControl
    {
        return new PageTitleControl($this->database);
    }

    protected function createComponentPageDocument(): PageDocumentControl
    {
        return new PageDocumentControl($this->database);
    }

    protected function createComponentPageSlug(): PageSlugControl
    {
        return new PageSlugControl($this->database);
    }

    protected function createComponentAdminBar(): AdminBarControl
    {
        return new AdminBarControl($this->database);
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    protected function createComponentFooter(): FooterControl
    {
        return new FooterControl($this->database);
    }

    /**
     * Content editable snippets
     */
    public function handleSnippet(): void
    {
        $this->database->table('snippets')->get($this->getParameter('snippetId'))->update([
            'content' => $this->getParameter('text')
        ]);
        exit();
    }

    /**
     * Content editable page title
     */
    public function handlePagetitle(): void
    {
        $this->database->table('pages')->where('id', $this->getParameter('editorId'))->update([
            'title' => $this->getParameter('text')
        ]);
        exit();
    }
}
