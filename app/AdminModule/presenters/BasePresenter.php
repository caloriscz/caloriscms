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

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
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
            if ($s < 60 && $a > 0) {
                $duration = '0:' . $s . '.';
            } elseif ($s >= 60) {
                $duration = ceil($s / 60) . ':' . ($s % 60) . '.';
            } else {
                $duration = '-';
            }

            return $duration;
        });

        $template->addFilter('toBaseName', function ($s) {
            $basename = basename($s);

            return $basename;
        });

        return $template;
    }

    protected function startup()
    {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            $this->template->isLoggedIn = TRUE;

            $this->template->member = $this->database->table("users")->get($this->user->getId());
        }

        // Set values from db
        $this->template->settings = $this->database->table("settings")->fetchPairs("setkey", "setvalue");

        $this->template->appDir = APP_DIR;
        $this->template->signed = TRUE;
        $this->template->langSelected = $this->translator->getLocale();
    }

}
