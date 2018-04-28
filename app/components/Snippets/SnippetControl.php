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

    public function render($snippetId)
    {
        $template = $this->getTemplate();
        $template->page = $this->database->table('snippets')->get($snippetId);

        if ($this->presenter->translator->getLocale() === $this->presenter->translator->getDefaultLocale()) {
            $snippet = $this->template->page->content;
        } else {
            $snippet = $this->template->page->{'content_' . $this->presenter->translator->getLocale()};
        }

        $template->snippet = $snippet;
        $template->snippetId = $snippetId;
        $template->setFile(__DIR__ . '/SnippetControl.latte');
        $template->render();
    }

}