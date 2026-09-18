<?php

namespace App\FrontModule\Presenters;

use Nette,
    Throwable,
    Tracy\ILogger;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use App\Security\ErrorContext;

/**
 * Error presenter.
 */
class ErrorPresenter extends Presenter
{
    // Render even when the original request used an unsupported HTTP method.
    public $allowedMethods = [];
    public $autoCanonicalize = false;

    /** @var ILogger */
    private $logger;

    /**
     * ErrorPresenter constructor.
     * @param ILogger $logger
     */
    public function __construct(ILogger $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * @param Throwable $exception
     * @throws Nette\Application\AbortException
     */
    public function renderDefault(Throwable $exception, ?Request $request = null): void
    {
        // Error pages must work without page/settings queries or site components.
        $this->setLayout(__DIR__ . '/../templates/Error/@layout.latte');
        $code = $exception instanceof BadRequestException ? ($exception->getHttpCode() ?: 404) : 500;
        $this->getHttpResponse()->setCode($code);
        $this->getHttpResponse()->setHeader('Cache-Control', 'no-store');
        $this->template->errorCode = $code;
        if ($exception instanceof BadRequestException) {
            // load template 403.latte or 404.latte or ... 4xx.latte
            $this->setView(\in_array($code, [403, 404, 405, 410], true) ? $code : '4xx');
            // log to access.log
            $this->logger->log(ErrorContext::describe($code, $request), 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            $this->logger->log(ErrorContext::describe($code, $request), 'access');
            $this->logger->log($exception, ILogger::EXCEPTION); // and log exception
        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->sendJson(['error' => true, 'code' => $code]);
        }
    }

}
