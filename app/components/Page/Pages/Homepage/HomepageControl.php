<?php
namespace Caloriscz\Page\Pages\Homepage;

use Caloriscz\Page\PageTitleControl;
use Caloriscz\Snippets\SnippetControl;
use Nette\Application\UI\Control;

class HomepageControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentBlogPreview()
    {
        $control = new \Caloriscz\Blog\BlogPreviewControl($this->database);
        return $control;
    }

    protected function createComponentEventsCalendar()
    {
        $control = new \EventsCalendarControl($this->database);
        return $control;
    }

    protected function createComponentCarouselBox()
    {
        $control = new \CarouselBoxControl($this->database);
        return $control;
    }

    protected function createComponentSnippet()
    {
        $control = new SnippetControl($this->database);
        return $control;
    }

    protected function createComponentPageTitle()
    {
        $control = new PageTitleControl($this->database);
        return $control;
    }


    public function render()
    {
        $template = $this->template;
        $template->page = $this->presenter->template->page;
        $template->setFile(__DIR__ . '/HomepageControl.latte');

        $template->render();
    }

}