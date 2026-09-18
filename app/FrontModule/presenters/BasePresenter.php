<?php

namespace App\FrontModule\Presenters;

use App\Forms\Helpdesk\HelpdeskControl;
use App\Forms\Pages\AdvancedSearchControl;
use Caloriscz\Appearance\CarouselBoxControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Navigation\AdminBarControl;
use Caloriscz\Navigation\FooterControl;
use Caloriscz\Navigation\HeadControl;
use Caloriscz\Navigation\NavigationControl;
use Caloriscz\Page\ContactControl;
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
    use \App\Security\CsrfProtectedMutation;

    private bool $inlineEditingAllowed = false;
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

        $locale = $this->getParameter('locale');
        if (\in_array($locale, ['cs', 'en'], true)) {
            $this->translator->setLocale($locale);
        } else {
            $this->translator->setLocale($this->translator->getDefaultLocale());
        }

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
                if ($this->getParameter('secretx') === $this->template->settings['site_cookie_whitelist']) {
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

        $member = $this->user->isLoggedIn()
            ? $this->database->table('users')->get($this->user->getId()) : null;
        $role = $member ? $member->ref('users_roles', 'users_roles_id') : null;
        $this->template->member = $member;
        $this->template->memberRole = $role;
        $this->template->isLoggedIn = $member && (int) $member->state === 1;
        if ($this->template->isLoggedIn) {
            $this->getHttpResponse()->setHeader('Cache-Control', 'private, no-store');
        }
        $this->inlineEditingAllowed = $member && $role
            && \App\Security\InlineEditing::allows($member->toArray(), $role->toArray());
        $this->template->inlineEditingEnabled = $this->inlineEditingEnabled();
        if ($this->inlineEditingEnabled()) {
            $this->template->inlineCsrfToken = $this->getCsrfToken();
            $this->getHttpResponse()->setHeader('Cache-Control', 'private, no-store');
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

    protected function createComponentCarouselBox(): CarouselBoxControl
    {
        return new CarouselBoxControl($this->database);
    }

    protected function createComponentPageContact(): ContactControl
    {
        return new ContactControl($this->database);
    }

    protected function createComponentHelpdesk(): HelpdeskControl
    {
        return new HelpdeskControl($this->database);
    }

    /**
     * Content editable snippets
     */
    public function handleSnippet(): void
    {
        $this->saveInlineEdit('snippet', 'snippetId');
    }

    /**
     * Content editable page title
     */
    public function handlePagetitle(): void
    {
        $this->saveInlineEdit('title', 'editorId');
    }

    public function inlineEditingEnabled(): bool
    {
        return $this->inlineEditingAllowed
            && (bool) $this->template->settings['site:admin:adminBarEnabled']
            && (bool) $this->template->member->adminbar_enabled;
    }

    public function getMutationToken(): string
    {
        return $this->getCsrfToken();
    }

    private function saveInlineEdit(string $kind, string $idField): void
    {
        if (!$this->inlineEditingEnabled()) {
            $this->error('Inline editing is not permitted.', 403);
        }
        $this->requireCsrfToken();
        $request = $this->getHttpRequest();
        $content = \App\Security\InlineEditing::save($this->database, $kind,
            $request->getPost($idField), $request->getPost('text'),
            $this->translator->getLocale(), $this->translator->getDefaultLocale());
        $this->sendJson(['saved' => true, 'content' => $content]);
    }
}
