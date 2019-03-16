<?php

namespace Caloriscz\Snippets;

use Nette\Application\UI\Control;
use Nette\Database\Context;

/**
 * Class SnippetControl
 * @package Caloriscz\Snippets
 */
class SnippetControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($snippetId): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->page = $this->database->table('snippets')->get($snippetId);

        if ($this->presenter->translator->getLocale() === $this->presenter->translator->getDefaultLocale()) {
            $snippet = $this->template->page->content;
        } else {
            $snippet = $this->template->page->{'content_' . $this->presenter->translator->getLocale()};
        }

        $role = $this->presenter->user->getRoles();
        $template->roleCheck = $this->database->table('users_roles')->get($role[0]);

        if ($template->roleCheck && $template->settings['site:admin:adminBarEnabled'] && $this->presenter->template->member->adminbar_enabled) {
            $template->enabled = true;
        } else {
            $template->enabled = false;
        }

        $template->snippet = $snippet;
        $template->snippetId = $snippetId;
        $template->setFile(__DIR__ . '/SnippetControl.latte');
        $template->render();
    }

}