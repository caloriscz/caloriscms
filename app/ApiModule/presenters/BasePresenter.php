<?php

namespace App\ApiModule\Presenters;

use App\Model\Api\ApiTokenAuthenticator;
use App\Model\Api\ApiTokenIdentity;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{

    public Explorer $database;
    private ApiTokenAuthenticator $apiTokenAuthenticator;
    private ?ApiTokenIdentity $apiIdentity = null;

    public function __construct(Explorer $database, ApiTokenAuthenticator $apiTokenAuthenticator)
    {
        parent::__construct();
        $this->database = $database;
        $this->apiTokenAuthenticator = $apiTokenAuthenticator;
    }

    public function startup(): void
    {
        parent::startup();

        $this->template->settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');
    }

    /**
     * @throws AbortException
     */
    protected function requireScope(string $scope): void
    {
        $identity = $this->getApiIdentity();

        if (!$identity->hasScope($scope)) {
            $this->sendApiError('Missing API scope: ' . $scope, 403);
        }
    }

    protected function getApiIdentity(): ApiTokenIdentity
    {
        if ($this->apiIdentity === null) {
            $this->apiIdentity = $this->apiTokenAuthenticator->authenticate(
                $this->getHttpRequest()->getHeader('Authorization')
            );
        }

        if ($this->apiIdentity === null) {
            $this->sendApiError('Invalid or missing API token', 401);
        }

        return $this->apiIdentity;
    }

    protected function getJsonBody(): array
    {
        $rawBody = $this->getHttpRequest()->getRawBody();

        if ($rawBody === '' || $rawBody === null) {
            return [];
        }

        $data = json_decode($rawBody, true);

        if (!is_array($data)) {
            $this->sendApiError('Invalid JSON request body', 400);
        }

        return $data;
    }

    protected function sendApiResponse(array $payload, int $statusCode = 200): void
    {
        $this->getHttpResponse()->setCode($statusCode);
        $this->sendJson($payload);
    }

    protected function sendApiError(string $message, int $statusCode): void
    {
        $this->sendApiResponse([
            'error' => [
                'message' => $message,
                'code' => $statusCode,
            ],
        ], $statusCode);
    }
}
