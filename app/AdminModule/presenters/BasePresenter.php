<?php
declare(strict_types=1);
namespace App\AdminModule\Presenters;

use App\Forms\Pages\EditorControl;
use Caloriscz\Menus\Admin\MainMenuControl;
use Caloriscz\Menus\PageTopMenuControl;
use Caloriscz\Utilities\PagingControl;
use Nette;

/**
 * @property-read \Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var Nette\Database\Context */
    public $database;

    /** @var \Kdyby\Doctrine\EntityManager @inject */
    public $em;

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /** @persistent */
    public $id;

    /** @var \Nette\Mail\IMailer @inject */
    public $mailer;

    /** @var Nette\Http\IRequest @inject */
    public $request;

    /** @var Nette\Http\IResponse @inject */
    public $response;

    /** @var string @persistent */
    public $ajax = 'on';

    /** @persistent */
    public $backlink = '';

    public function __construct(Nette\Database\Context $database, Nette\Mail\IMailer $mailer)
    {
        parent::__construct();
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /**
     * Common handler for grid operations
     * @param $operation
     * @param $id
     * @throws Nette\Application\AbortException
     */
    public function handleOperations($operation, $id)
    {
        if ($id) {
            $row = implode(', ', $id);
            $this->flashMessage("Process operation '$operation' for row with id: $row...");
        } else {
            $this->flashMessage('No rows selected.', 'error');
        }
        if ($this->isAjax()) {
            isset($this['grid']) && $this['grid']->reload();
            $this->redrawControl('flashes');
        } else {
            $this->redirect($operation, ['idm' => $id]);
        }
    }

    /**
     * @param null $class
     * @return Nette\Application\UI\ITemplate
     */
    protected function createTemplate($class = null)
    {
        $template = parent::createTemplate($class);
        $template->addFilter(null, '\Filters::common');

        return $template;
    }

    /**
     * @throws Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();

        // Login check
        if ($this->getName() !== 'Admin:Sign') {

            $role = $this->user->getRoles();
            $roleCheck = $this->database->table('users_roles')->get($role[0]);

            if ($roleCheck && $roleCheck->sign === 'guest') {
                $this->flashMessage($this->translator->translate('messages.sign.invalidLogin'), 'error');
                $this->redirect(':Admin:Sign:in');
            }

            if ($this->user->isLoggedIn()) {
            } else {
                if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                    $this->flashMessage($this->translator->translate('messages.sign.youWereLoggedIn'), 'note');
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
        if ($this->request->getCookie('langugage_admin') === '') {
            $this->translator->setLocale($this->translator->getDefaultLocale());
        } else {
            $this->translator->setLocale($this->request->getCookie('language_admin'));
        }
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
        return new EditorControl($this->database, $this->em);
    }

    /**
     * @return MainMenuControl
     */
    protected function createComponentMainMenu(): MainMenuControl
    {
        return new MainMenuControl($this->em);
    }

    /**
     * @return PageTopMenuControl
     */
    protected function createComponentPageTopMenu(): PageTopMenuControl
    {
        return new PageTopMenuControl($this->database);
    }
}
