<?php

use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;

/**
 * List of languages for administration
 * Class LangSelectorControl
 */
class LangSelectorControl extends Control
{

    /** @var EntityManager @inject */
    public $database;

    /**
     * LangSelectorControl constructor.
     * @param EntityManager $em
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $this->template->id = $this->getPresenter()->getParameter('id');
        $this->template->languages = $this->database->table('languages');

        $this->template->langSelected = $this->getPresenter()->getParameter('l');
        $this->template->arr = $this->getPresenter()->getParameters();

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->setFile(__DIR__ . '/LangSelectorControl.latte');
        $this->template->render();
    }

}
