<?php
declare(strict_types=1);

namespace App\ApiModule\Presenters;

use App\Model\Api\ContentApiService;
use InvalidArgumentException;

class PagesPresenter extends BasePresenter
{
    private ContentApiService $contentApi;

    public function __construct(
        \Nette\Database\Explorer $database,
        \App\Model\Api\ApiTokenAuthenticator $apiTokenAuthenticator,
        ContentApiService $contentApi
    ) {
        parent::__construct($database, $apiTokenAuthenticator);
        $this->contentApi = $contentApi;
    }

    public function actionDefault(?int $id = null): void
    {
        $method = $this->getHttpRequest()->getMethod();

        if ($method === 'GET') {
            $this->requireScope('pages:read');
            $id === null ? $this->listPages() : $this->readPage($id);
            return;
        }

        if ($method === 'POST' && $id === null) {
            $this->requireScope('pages:write');
            $this->createPage();
            return;
        }

        if ($method === 'PATCH' && $id !== null) {
            $this->requireScope('pages:write');
            $this->updatePage($id);
            return;
        }

        if ($method === 'DELETE' && $id !== null) {
            $this->requireScope('pages:write');
            $this->deletePage($id);
            return;
        }

        $this->sendApiError('Method not allowed', 405);
    }

    private function listPages(): void
    {
        $this->sendApiResponse([
            'items' => $this->contentApi->listPages($this->getHttpRequest()->getQuery()),
        ]);
    }

    private function readPage(int $id): void
    {
        $page = $this->contentApi->getPage($id);

        if (!$page) {
            $this->sendApiError('Page not found', 404);
        }

        $this->sendApiResponse($page);
    }

    private function createPage(): void
    {
        $data = $this->getJsonBody();
        $unknownField = $this->contentApi->findUnknownPageField($data);

        if ($unknownField !== null) {
            $this->sendApiError('Unknown field: ' . $unknownField, 400);
        }

        try {
            $page = $this->contentApi->createPage($data, (int) $this->getApiIdentity()->user->id);
        } catch (InvalidArgumentException $e) {
            $this->sendApiError($e->getMessage(), 400);
        }

        $this->sendApiResponse($page, 201);
    }

    private function updatePage(int $id): void
    {
        $data = $this->getJsonBody();
        $unknownField = $this->contentApi->findUnknownPageField($data);

        if ($unknownField !== null) {
            $this->sendApiError('Unknown field: ' . $unknownField, 400);
        }

        try {
            $page = $this->contentApi->updatePage($id, $data);
        } catch (InvalidArgumentException $e) {
            $this->sendApiError($e->getMessage(), 400);
        }

        if (!$page) {
            $this->sendApiError('Page not found', 404);
        }

        $this->sendApiResponse($page);
    }

    private function deletePage(int $id): void
    {
        if (!$this->contentApi->unpublishPage($id)) {
            $this->sendApiError('Page not found', 404);
        }

        $this->sendApiResponse(['deleted' => false, 'public' => 0]);
    }
}
