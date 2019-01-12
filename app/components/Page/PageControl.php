<?php

namespace Caloriscz\Page;

use Caloriscz\Appearance\CarouselBoxControl;
use Caloriscz\Blog\BlogListControl;
use Caloriscz\Blog\BlogPreviewControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\NavbarMenuControl;
use Caloriscz\Snippets\SnippetControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentBlogPreview(): BlogPreviewControl
    {
        return new BlogPreviewControl($this->database);
    }

    protected function createComponentSnippet(): SnippetControl
    {
        return new SnippetControl($this->database);
    }

    protected function createComponentPageTitle(): PageTitleControl
    {
        return new PageTitleControl($this->database);
    }

    protected function createComponentPageGallery(): PageGalleryControl
    {
        return new PageGalleryControl($this->database);
    }


    protected function createComponentPageDocument(): PageDocumentControl
    {
        return new PageDocumentControl($this->database);
    }

    protected function createComponentBlogList(): BlogListControl
    {
        return new BlogListControl($this->database);
    }

    public function render(): void
    {
        // Choose tempalte according to Settings
        $settings = $this->getPresenter()->template->settings;

        $this->template->page = $this->presenter->template->page;
        $this->template->setFile(__DIR__ . '/' . $settings['pages_template'] . 'Control.latte');
        $this->template->render();
    }

}