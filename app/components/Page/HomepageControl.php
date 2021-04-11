<?php

namespace Caloriscz\Page;

use App\Forms\Helpdesk\HelpdeskControl;
use Caloriscz\Appearance\CarouselBoxControl;
use Caloriscz\Blog\BlogPreviewControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\NavbarMenuControl;
use Caloriscz\Snippets\SnippetControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class HomepageControl extends Control
{

    /** @var Explorer */
    public $database;

    public function __construct(Explorer $database)
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
        // Choose template according to Settings
        $settings = $this->getPresenter()->template->settings;

        $this->template->settings = $settings;
        $this->template->page = $this->presenter->template->page;
        $this->template->setFile(__DIR__ . '/' . $settings['homepage_template'] . 'Control.latte');
        $this->template->render();
    }

}