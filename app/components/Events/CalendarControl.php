<?php

use Nette\Application\UI\Control;

class EventsCalendarControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CalendarControl.latte');

        if ($this->presenter->getParameter('y')) {
            $template->year = $this->presenter->getParameter('y');
        } else {
            $template->year = date('Y');
        }

        if ($this->presenter->getParameter('m')) {
            $template->month = $this->presenter->getParameter('m');
        } else {
            $template->month = date('m');
        }

        $events = $this->database->table("events")
                ->select("DAYOFMONTH(date_event) AS datestamp, MONTH(`date_event`) AS monthstamp, YEAR(`date_event`) AS yearstamp, pages.title")
                ->having("monthstamp = ? AND yearstamp = ?", $template->month, $template->year)
                ->order("date_event");

        foreach ($events as $event) {
            $eventDay[$event->datestamp] .= $event->title . ';';
        }

        $template->eventsDates = $eventDay;

        $template->prevMonth = date('m', mktime(0, 0, 0, $template->month - 1, 1, $template->year));
        $template->prevYear = date('Y', mktime(0, 0, 0, $template->month - 1, 1, $template->year));
        $template->nextMonth = date('m', mktime(0, 0, 0, $template->month + 1, 1, $template->year));
        $template->nextYear = date('Y', mktime(0, 0, 0, $template->month + 1, 1, $template->year));

        $dateChosen = $template->year . '-' . $template->month . '-01';
        $template->firstDayofMonth = date('N', strtotime(date($dateChosen)));
        $lastDay = date('Y-m-t', strtotime(date($dateChosen)));
        $template->lastDayofMonth = date('N', strtotime($lastDay));
        $template->daysInMonth = date('t', strtotime($lastDay));
        $template->nameMonth = $this->getMonthNameInCzech(date('n', strtotime($dateChosen)));
        $template->nameYear = date('Y', strtotime($dateChosen));

        $template->render();
    }
    
    function getMonthNameInCzech($monthNumber)
    {
        $months = array(
          1 => 'leden',
          2 => 'únor',
          3 => 'březen',
          4 => 'duben',
          5 => 'květen',
          6 => 'červen',
          7 => 'červenec',
          8 => 'srpen',
          9 => 'září',
          10 => 'říjen',
          11 => 'listopad',
          12 => 'prosinec',
        );
        
        return $months[$monthNumber];
    }

}
