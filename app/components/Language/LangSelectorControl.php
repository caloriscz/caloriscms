<?php

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

/**
 * List of languages for administration
 * Class LangSelectorControl
 */
class LangSelectorControl extends Control
{

    /** @var Explorer */
    public $database;

    /**
     * LangSelectorControl constructor.
     * @param Explorer $database
     */
    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $this->template->id = $this->getPresenter()->getParameter('id');
        $this->template->languages = $this->database->table('languages')->where('used = 1');

        $this->template->langSelected = $this->getPresenter()->getParameter('l');
        $this->template->arr = $this->getPresenter()->getParameters();

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->setFile(__DIR__ . '/LangSelectorControl.latte');
        $this->template->render();
    }

}
