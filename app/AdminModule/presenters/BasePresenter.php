<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Forms\Pages\EditorControl;
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

    /** @var Explorer */
    public $database;

    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

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

            $role = $this->user->getRoles();
            $roleCheck = $this->database->table('users_roles')->get($role[0]);

            if ($roleCheck && $roleCheck->sign === 'guest') {
                $this->flashMessage('Neplatné přihlášení', 'error');
                $this->redirect(':Admin:Sign:in');
            }

            if (!$this->user->isLoggedIn()) {
                if ($this->user->logoutReason === UserStorage::INACTIVITY) {
                    $this->flashMessage('Byli jste odhlášeni', 'note');
                }
                $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
            }
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->template->isLoggedIn = true;
            $this->template->member = $this->database->table('users')->get($this->getUser()->getId());
        } else {
            $this->template->isLoggedIn = false;
            $this->template->member = false;
        }

        // Set values from db
        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');

        $this->template->appDir = APP_DIR;
        $this->template->signed = true;
        $this->template->langSelected = $this->translator->getLocale();

        // Set language from cookie
        $this->translator->setLocale($this->translator->getDefaultLocale());
    }

    /**
     * @return PagingControl
     */
    protected function createComponentPaging(): PagingControl
    {
        return new PagingControl;
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
