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
        $code = $exception instanceof BadRequestException ? ($exception->getHttpCode() ?: 404) : 500;
        // Preserve the original failure even if the response can no longer change.
        $this->logger->log(ErrorContext::describe($code, $request), 'access');
        if (!$exception instanceof BadRequestException) {
            $this->logger->log($exception, ILogger::EXCEPTION);
        }

        $httpResponse = $this->getHttpResponse();
        if ($httpResponse->isSent()) {
            $this->logger->log('Error response headers already sent; body left unchanged; sent status '
                . $httpResponse->getCode() . '; ' . ErrorContext::describe($code, $request), ILogger::WARNING);
            // Appending HTML or JSON would corrupt an already-started response.
            // JsonResponse also tries to set Content-Type when it is sent.
            $this->sendResponse(new Nette\Application\Responses\VoidResponse());
        }

        // Error pages must work without page/settings queries or site components.
        $httpResponse->setCode($code);
        $httpResponse->setHeader('Cache-Control', 'no-store');
        $this->setLayout(__DIR__ . '/../templates/Error/@layout.latte');
        $this->template->errorCode = $code;
        $this->setView($exception instanceof BadRequestException
            ? (\in_array($code, [403, 404, 405, 410], true) ? (string) $code : '4xx') : '500');

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->sendJson(['error' => true, 'code' => $code]);
        }
    }

}
