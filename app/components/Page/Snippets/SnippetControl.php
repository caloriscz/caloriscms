<?php
namespace Caloriscz\Snippets;

use Nette\Application\UI\Control;

class SnippetControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($snippetId)
    {
        $template = $this->template;

        $template->page = $this->database->table('snippets')->get($snippetId);

        if ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
            $snippet = $template->page->content;
        } else {
            $snippet = $template->page->{'content_' . $this->presenter->translator->getLocale()};
        }

        $template->snippet = $snippet;
        $template->snippetId = $snippetId;

        $template->setFile(__DIR__ . '/SnippetControl.latte');

        $template->render();
    }

}