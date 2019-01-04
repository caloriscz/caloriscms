<?php

namespace Caloriscz\Page;

use Caloriscz\Appearance\CarouselBoxControl;
use Caloriscz\Blog\BlogPreviewControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\NavbarMenuControl;
use Caloriscz\Snippets\SnippetControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class HomepageControl extends Control
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

    protected function createComponentCarouselBox(): CarouselBoxControl
    {
        return new CarouselBoxControl($this->database);
    }

    protected function createComponentSnippet(): SnippetControl
    {
        return new SnippetControl($this->database);
    }

    protected function createComponentPageTitle(): PageTitleControl
    {
        return new PageTitleControl($this->database);
    }

    protected function createComponentNavbarMenu(): NavbarMenuControl
    {
        return new NavbarMenuControl($this->database);
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    public function render(): void
    {
        // Choose tempalte according to Settings
        $settings = $this->getPresenter()->template->settings;

        $this->template->page = $this->presenter->template->page;
        $this->template->setFile(__DIR__ . '/' . $settings['homepage_template'] . 'Control.latte');
        $this->template->render();
    }

}