<?php
namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageDocumentControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function parseSnippets($s) {
        preg_match_all("/\[snippet\=\"([0-9]{1,10})\"\]/s", $s, $valsimp, PREG_SET_ORDER);
        if (count($valsimp) > 0) {
            foreach ($valsimp as $n => $nValue) {
                $snippet = $this->database->table('snippets')->get($valsimp[$n][1]);
                if ($snippet) {
                    if ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
                        $results = $snippet->content;
                    } else {
                        $results = $snippet->{'content_' . $this->presenter->translator->getLocale()};
                    }
                } else {
                    $results = null;
                }
                $s = str_replace($valsimp[$n][0], '$results', $s);
            }
        }
        preg_match_all("/\[file\=([0-9]{1,10})\]/s", $s, $valsimp, PREG_SET_ORDER);
        if (count($valsimp) > 0) {
            foreach ($valsimp as $n => $nValue) {
                $snippet = $this->database->table("media")->get($valsimp[$n][1]);
                if ($snippet) {
                    $results = '/media/' . $snippet->pages_id . '/' . $snippet->name;
                } else {
                    $results = null;
                }
                $s = str_replace($valsimp[$n][0], "$results", $s);
            }
        }
        return $s;
    }

    public function render($page): void
    {
        $this->template->page = $page;

        if ($this->getPresenter()->translator->getLocale() === $this->getPresenter()->translator->getDefaultLocale()) {
            $document = $page->document;
        } else {
            $document = $page->{'document_' . $this->getPresenter()->translator->getLocale()};
        }

        $this->template->document = $this->parseSnippets($document);
        $this->template->setFile(__DIR__ . '/PageDocumentControl.latte');
        $this->template->render();
    }

}