<?php
namespace Caloriscz\Navigation\Homepage;

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
        $control = new \BlogPreviewControl($this->database);
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

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/HomepageControl.latte');

        $template->render();
    }

}