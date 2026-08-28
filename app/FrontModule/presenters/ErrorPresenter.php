<?php

namespace App\FrontModule\Presenters;

use Nette,
    Throwable,
    Tracy\ILogger;
use Nette\Application\BadRequestException;
use Nette\Database\Explorer;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

    /** @var ILogger */
    private $logger;

    /**
     * ErrorPresenter constructor.
     * @param ILogger $logger
     * @param Explorer $database
     */
    public function __construct(ILogger $logger, Explorer $database)
    {
        $this->logger = $logger;
        $this->database = $database;
    }

    /**
     * @param Throwable $exception
     * @throws Nette\Application\AbortException
     */
    public function renderDefault(Throwable $exception): void
    {
        if ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // load template 403.latte or 404.latte or ... 4xx.latte
            $this->setView(\in_array($code, [403, 404, 405, 410], true) ? $code : '4xx');
            // log to access.log
            $this->logger->log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            $this->logger->log($exception, ILogger::EXCEPTION); // and log exception
        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->payload->error = true;
            $this->terminate();
        }
    }

}
