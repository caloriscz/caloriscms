<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Media\DropZoneControl;
use Caloriscz\Menus\Admin\MainMenuControl;
use Caloriscz\Menus\Admin\PageTopMenuControl;
use Caloriscz\Page\Editor\EditorControl;
use Caloriscz\Page\Editor\EditorSettingsControl;
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

    public function __construct(\Nette\Database\Context $database, \Nette\Mail\IMailer $mailer)
    {
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
            $this->flashMessage("Process operation '$operation' for row with id: $row...", 'info');
        } else {
            $this->flashMessage('No rows selected.', 'error');
        }
        if ($this->isAjax()) {
            isset($this['grid']) && $this['grid']->reload();
            $this->redrawControl('flashes');
        } else {
            $this->redirect($operation, array('idm' => $id));
        }
    }

    /** @persistent */
    public $backlink = '';

    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->addFilter(NULL, '\Filters::common');

        return $template;
    }

    protected function startup()
    {
        parent::startup();

        // Login check
        if ($this->getName() != 'Admin:Sign') {
            $role = $this->user->getRoles();
            $roleCheck = $this->database->table('users_roles')->get($role[0]);

            if ($roleCheck->admin_access == 0) {
                $this->flashMessage($this->translator->translate('messages.sign.invalidLogin'), 'error');
                $this->redirect(':Admin:Sign:in');
            }

            if ($this->user->isLoggedIn()) {
            } else {
                if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                    $this->flashMessage($this->translator->translate('messages.sign.youWereLoggedIn'), 'note');
                }
                $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
            }
        }


        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = TRUE;

            $this->template->member = $this->database->table('users')->get($this->user->getId());
        }

        // Set values from db
        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');

        $this->template->appDir = APP_DIR;
        $this->template->signed = TRUE;
        $this->template->langSelected = $this->translator->getLocale();

        // Set language from cookie
        if ($this->request->getCookie('langugage_admin') == '') {
            $this->translator->setLocale($this->translator->getDefaultLocale());
        } else {
            $this->translator->setLocale($this->request->getCookie('language_admin'));
        }
    }

    protected function createComponentPaging()
    {
        $control = new PagingControl;
        return $control;
    }

    protected function createComponentEditor()
    {
        $control = new EditorControl($this->database, $this->em);
        return $control;
    }

    protected function createComponentEditorSettings()
    {
        $control = new EditorSettingsControl($this->database);
        return $control;
    }

    protected function createComponentDropZone()
    {
        $control = new DropZoneControl($this->database);
        return $control;
    }

    protected function createComponentImageBrowser()
    {
        $control = new ImageBrowserControl($this->database);
        return $control;
    }

    protected function createComponentMainMenu()
    {
        $control = new MainMenuControl($this->em);
        return $control;
    }

    protected function createComponentPageTopMenu()
    {
        $control = new PageTopMenuControl($this->database);
        return $control;
    }

}
