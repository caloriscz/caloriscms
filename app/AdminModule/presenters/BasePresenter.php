<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Forms\Pages\EditorControl;
use App\Security\AdminPermissions;
use Caloriscz\Menus\Admin\MainMenuControl;
use Caloriscz\Menus\PageTopMenuControl;
use Caloriscz\Utilities\PagingControl;
use Nette\Application\AbortException;
use Nette\Database\Explorer;
use Symfony\Component\Translation\Translator;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Mail\Mailer;
use Nette\Security\UserStorage;

/**
 * @property-read \Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 */
abstract class BasePresenter extends Presenter
{
    use \App\Security\CsrfProtectedMutation;

    public Explorer $database;

    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public Translator $translator;

    /** @persistent */
    public $id;

    /** @var Mailer @inject */
    public $mailer;

    /** @var IRequest @inject */
    public $request;

    /** @var IResponse @inject */
    public $response;

    /** @var string @persistent */
    public $ajax = 'on';

    public function __construct(Explorer $database, Mailer $mailer)
    {
        parent::__construct();
        $this->database = $database;
        $this->mailer = $mailer;
    }

    protected function beforeRender()
    {
        $this->template->addFilter('toBaseName', function ($s): string {
            return basename($s);
        });

        $this->template->addFilter('numericday', function ($s): string {
            $names = [1 => 'Pondělí', 2 => 'Úterý', 3 => 'Středa', 4 => 'Čtvrtek', 5 => 'Pátek', 6 => 'Sobota', 7 => 'Neděle'];
            return $names[$s];
        });
    }

    /**
     * @throws AbortException
     */
    protected function startup()
    {
        parent::startup();

        // Login check
        if ($this->getName() !== 'Admin:Sign') {

            if (!$this->user->isLoggedIn()) {
                if ($this->user->logoutReason === UserStorage::LOGOUT_INACTIVITY) {
                    $this->flashMessage('Byli jste odhlášeni', 'note');
                }
                $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
            }
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->template->isLoggedIn = true;
            $this->template->member = $this->database->table('users')->get($this->getUser()->getId());
            $this->template->memberRole = $this->template->member
                ? $this->template->member->ref('users_roles', 'users_roles_id')
                : false;
        } else {
            $this->template->isLoggedIn = false;
            $this->template->member = false;
            $this->template->memberRole = false;
        }

        $role = $this->template->memberRole ? $this->template->memberRole->toArray() : [];
        if ($this->getName() !== 'Admin:Sign'
            && (!$this->template->member || (int) $this->template->member->state !== 1
                || !AdminPermissions::allows($role, 'sign'))) {
            $this->error('Admin access denied.', 403);
        }
        AdminPermissions::requireRequest($role, $this->getName(), $this->getAction(), $this->getSignal());

        // Nette validates POST forms through BootstrapUIForm. Other signals are
        // mutations unless explicitly limited to a display preference or read.
        $signal = $this->getSignal();
        if ($signal && strtolower($signal[1]) !== 'submit') {
            $preference = ($this->getName() === 'Admin:Pages' && $signal === ['', 'view'])
                || $signal === ['editor', 'toggle'];
            $request = $this->getHttpRequest();
            // elFinder's connector resolves the command and method from the PHP
            // superglobals ($_SERVER['REQUEST_METHOD'], $_GET), not from Nette's
            // parsed request. A forged query key (cmd=open&%20cmd=zipdl), a
            // NUL-truncated one (download%00x=0), or an X-HTTP-Method-Override made
            // Nette and the connector disagree about what would run. Decide from the
            // same superglobals, and only for a request that carries no POST field,
            // no upload and no raw body (the connector can reparse a raw body).
            $command = $_GET['cmd'] ?? null;
            $fileRead = $signal === ['elfinder', 'options']
                && in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)
                && $_POST === [] && $_FILES === []
                && ($request->getRawBody() ?? '') === '' && is_string($command)
                && (in_array($command, ['open', 'tree', 'parents', 'tmb', 'file', 'ls', 'size', 'dim', 'info', 'search', 'get', 'url'], true)
                    // The client fetches a prepared ZIP with a plain link, always with
                    // download=1. elFinder prepares one when the value is empty, so
                    // download=0 and download= need the token like any other mutation.
                    || ($command === 'zipdl' && ($_GET['download'] ?? null) === '1'));
            if (!$preference && !$fileRead) {
                $this->requireCsrfToken();
            }
        }
        // Start before templates render CSRF-protected forms.
        $this->getSession()->start();
        $this->getHttpResponse()->setHeader('Cache-Control', 'private, no-store');

        // Set values from db
        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');

        $this->template->appDir = APP_DIR;
        $this->template->adminScriptVersion = substr(hash_file('sha256', __DIR__ . '/../../../www/js/caloris-admin.js'), 0, 16);
        $this->template->adminBundleVersion = substr(hash_file('sha256', __DIR__ . '/../../../www/js/all-back.js'), 0, 16);
        $this->template->signed = true;
        $this->template->langSelected = $this->translator->getLocale();

        // Set language from cookie
        $this->translator->setLocale($this->translator->getDefaultLocale());
    }

    /**
     * The row of $table named by the id parameter. A missing, non-numeric or
     * unknown id is a 404 before any form or template reads the row.
     * @throws \Nette\Application\BadRequestException
     */
    protected function requireRecord(string $table): \Nette\Database\Table\ActiveRow
    {
        $id = $this->getParameter('id');
        $row = is_scalar($id) && preg_match('/^[1-9][0-9]{0,9}$/D', (string) $id)
            ? $this->database->table($table)->get((int) $id)
            : null;
        if ($row === null) {
            $this->error('Record not found.', 404);
        }

        return $row;
    }

    /**
     * @return PagingControl
     */
    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl;
    }

    public function getMutationToken(): string
    {
        return $this->getCsrfToken();
    }

    /**
     * @return EditorControl
     */
    protected function createComponentEditor(): EditorControl
    {
        return new EditorControl($this->database);
    }

    /**
     * @return MainMenuControl
     */
    protected function createComponentMainMenu(): MainMenuControl
    {
        return new MainMenuControl($this->database);
    }

    /**
     * @return PageTopMenuControl
     */
    protected function createComponentPageTopMenu(): PageTopMenuControl
    {
        return new PageTopMenuControl($this->database);
    }
}
