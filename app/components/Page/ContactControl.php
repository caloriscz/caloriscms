<?php

namespace Caloriscz\Page;

use App\Forms\Helpdesk\HelpdeskControl;
use Caloriscz\Snippets\SnippetControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class ContactControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSnippet(): SnippetControl
    {
        return new SnippetControl($this->database);
    }

    protected function createComponentPageTitle(): PageTitleControl
    {
        return new PageTitleControl($this->database);
    }

    protected function createComponentPageDocument(): PageDocumentControl
    {
        return new PageDocumentControl($this->database);
    }

    protected function createComponentHelpdesk(): HelpdeskControl
    {
        return new HelpdeskControl($this->database);
    }

    protected function createComponentContact(): \Caloriscz\Contact\ContactControl
    {
        return new \Caloriscz\Contact\ContactControl($this->database);
    }


    public function render(): void
    {
        $template = $this->getTemplate();
        // Choose template according to Settings
        $settings = $this->getPresenter()->template->settings;
        $template->settings = $settings;
        $template->page = $this->database->table('pages')->get(2);

        // Main contact
        $template->setFile(__DIR__ . '/' . $template->settings['contacts_template'] . 'Control.latte');
        $template->render();
    }

}