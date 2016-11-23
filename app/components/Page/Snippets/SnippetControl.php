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

    public function render($snippet)
    {
        $template = $this->template;

        $template->page = $this->database->table("snippets")->get($snippet);

        if ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
            $snippet = $template->page->content;
        } else {
            $snippet = $template->page->{'content_' . $this->presenter->translator->getLocale()};
        }

        $template->snippet = $snippet;

        $template->setFile(__DIR__ . '/SnippetControl.latte');

        $template->render();
    }

}