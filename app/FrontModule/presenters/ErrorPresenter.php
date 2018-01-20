<?php

namespace App\FrontModule\Presenters;

use Nette,
    Tracy\ILogger;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

    /** @var ILogger */
    private $logger;

    public function __construct(ILogger $logger, Nette\Database\Context $database)
    {
        $this->logger = $logger;
        $this->database = $database;
    }

    /**
     * @param $exception
     * @throws Nette\Application\AbortException
     */
    public function renderDefault($exception)
    {
        if ($exception instanceof Nette\Application\BadRequestException) {
            $code = $exception->getCode();
            // load template 403.latte or 404.latte or ... 4xx.latte
            $this->setView(in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx');
            // log to access.log
            $this->logger->log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            exit();
            $this->setView('500'); // load template 500.latte
            $this->logger->log($exception, ILogger::EXCEPTION); // and log exception
        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        }
    }

}
