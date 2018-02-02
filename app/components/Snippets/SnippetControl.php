<?php
namespace Caloriscz\Snippets;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class SnippetControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($snippetId)
    {
        $template = $this->getTemplate();
        $template->page = $this->database->table('snippets')->get($snippetId);

        if ($this->presenter->translator->getLocale() === $this->presenter->translator->getDefaultLocale()) {
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