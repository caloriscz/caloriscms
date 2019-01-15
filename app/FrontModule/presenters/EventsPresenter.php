<?php
namespace App\FrontModule\Presenters;

use Caloriscz\Events\SignEventControl;
use Nette\Utils\Paginator;

/**
 * Event presenter.
 */
class EventsPresenter extends BasePresenter
{

    protected function createComponentSignEvent(): SignEventControl
    {
        return new SignEventControl($this->database);
    }

    public function renderDefault(): void
    {
        $type = 1;

        if ($type === 1) {
            $events = $this->database->table('pages')
                ->where('pages_types_id = 3');
        } else {
            $events = $this->database->table('pages')
                ->select(':events.id, pages.id, pages.title, pages.pages_types_id, :events.date_event, :events.date_event_end, 
            :events.all_day, :events.contact, public, DATEDIFF(NOW(), 
            :events.date_event) AS diffDate')
                ->where('pages_types_id = 3');
        }

        $paginator = new Paginator();
        $paginator->setItemCount($events->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page'));

        $type = 1;
        $order = 'title';

        if ($type == 0) {
            $order = 'diffDate DESC';
        }

        $this->template->events = $events->order($order)->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;
        $this->template->args = $this->getParameters();

        $this->template->viewtype = $this->context->httpRequest->getCookie('viewtype');
    }
}