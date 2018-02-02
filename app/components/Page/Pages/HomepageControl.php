<?php
namespace Caloriscz\Page\Pages;

use Caloriscz\Appearance\CarouselBoxControl;
use Caloriscz\Blog\BlogPreviewControl;
use Caloriscz\Page\PageTitleControl;
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

    protected function createComponentBlogPreview()
    {
        return new BlogPreviewControl($this->database);
    }

    protected function createComponentCarouselBox()
    {
        return new CarouselBoxControl($this->database);
    }

    protected function createComponentSnippet()
    {
        return new SnippetControl($this->database);
    }

    protected function createComponentPageTitle()
    {
        return new PageTitleControl($this->database);
    }

    public function render()
    {
        $this->template->page = $this->presenter->template->page;
        $this->template->setFile(__DIR__ . '/HomepageControl.latte');
        $this->template->render();
    }

}