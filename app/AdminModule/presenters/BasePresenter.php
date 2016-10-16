<?php

namespace App\AdminModule\Presenters;

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

    /** @var \BaseForm @inject */
    public $baseFormFactory;

    /** @persistent */
    public $id;

    /** @var \Nette\Mail\IMailer @inject */
    public $mailer;

    public function __construct(\Nette\Database\Context $database, \Nette\Mail\IMailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /** @var string @persistent */
    public $ajax = 'on';

    /**
     * Common handler for grid operations.
     * @param string $operation
     * @param array $id
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
        $template->addFilter('ago', function ($s, $add = 0) {
            $date = new \DateTime();
            $date->setDate(date('Y', strtotime($s)), date('m', strtotime($s)), date('d', strtotime($s)));
            $interval = $date->diff(new \DateTime('now'));
            $daysAgo = $interval->format('%a days');

            return $daysAgo;
        });

        $template->addFilter('round', function ($s, $nr = 2) {
            $rounding = round($s, $nr);

            return $rounding;
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

        $template->addFilter('numericday', function ($s) {
            $nazvy = array(
                $this->translator->translate('dictionary.days.Sunday'),
                $this->translator->translate('dictionary.days.Monday'),
                $this->translator->translate('dictionary.days.Tuesday'),
                $this->translator->translate('dictionary.days.Wednesday'),
                $this->translator->translate('dictionary.days.Thursday'),
                $this->translator->translate('dictionary.days.Friday'),
                $this->translator->translate('dictionary.days.Saturday'));

            return $nazvy[$s];
        });

        $template->addFilter('toBaseName', function ($s) {
            $basename = basename($s);

            return $basename;
        });

        $template->addFilter('dateDiff', function ($s, $t) {
            $datetime1 = date_create($s);
            $datetime2 = date_create($t);
            $interval = date_diff($datetime1, $datetime2);

            return $interval->format('%R%a days');
        });

        return $template;
    }

    protected function startup()
    {
        parent::startup();

        // Login check
        if ($this->getName() != 'Admin:Sign') {
            $role = $this->user->getRoles();
            $roleCheck = $this->database->table("users_roles")->get($role[0]);

            if ($roleCheck->admin_access == 0) {
                $this->flashMessage($this->translator->translate('messages.sign.invalidLogin'), "error");
                $this->redirect(':Admin:Sign:in');
            }

            if ($this->user->isLoggedIn()) {
            } else {
                if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                    $this->flashMessage($this->translator->translate('messages.sign.youWereLoggedIn'), "note");
                }
                $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
            }
        }


        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = TRUE;

            $this->template->member = $this->database->table("users")->get($this->user->getId());
        }

        // Set values from db
        $this->template->settings = $this->database->table("settings")->fetchPairs("setkey", "setvalue");

        $this->template->appDir = APP_DIR;
        $this->template->signed = TRUE;
        $this->template->langSelected = $this->translator->getLocale();
		
        // Set language from cookie
        if ($this->context->httpRequest->getCookie('language_admin') == '') {
            $this->translator->setLocale($this->translator->getDefaultLocale());
        } else {
            $this->translator->setLocale($this->context->httpRequest->getCookie('language_admin'));
        }
    }

    protected function createComponentPaging()
    {
        $control = new \PagingControl;
        return $control;
    }

    protected function createComponentAdminPanel()
    {
        $control = new \Caloriscz\Menus\Admin\AdminPanelControl($this->database);
        return $control;
    }

    protected function createComponentEditor()
    {
        $control = new \Caloriscz\Page\EditorControl($this->database);
        return $control;
    }

    protected function createComponentImageBrowser()
    {
        $control = new \Caloriscz\Media\ImageBrowserControl($this->database);
        return $control;
    }

    protected function createComponentDropZone()
    {
        $control = new \DropZoneControl($this->database);
        return $control;
    }

    protected function createComponentImageUpload()
    {
        $control = new \ImageUploadControl($this->database);
        return $control;
    }

    protected function createComponentAdminCategoryPanel()
    {
        $control = new \Caloriscz\Menus\Admin\AdminCategoryPanelControl($this->database);
        return $control;
    }

    protected function createComponentMainMenu()
    {
        $control = new \Caloriscz\Menus\Admin\MainMenuControl($this->database);
        return $control;
    }
	
    protected function createComponentLangSelector()
    {
        $control = new \LangSelectorControl($this->database);
        return $control;
    }

    protected function createComponentPageTopMenu()
    {
        $control = new \Caloriscz\Menus\Admin\PageTopMenuControl($this->database);
        return $control;
    }

}
