<?php
declare(strict_types=1);

namespace App\ApiModule\Presenters;

use App\Model\Api\ContentApiService;
class MediaPresenter extends BasePresenter
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

    public function actionDefault(): void
    {
        if ($this->getHttpRequest()->getMethod() !== 'GET') {
            $this->sendApiError('Method not allowed', 405);
        }

        $this->requireScope('media:read');
        $this->sendApiResponse([
            'items' => $this->contentApi->listMedia($this->getHttpRequest()->getQuery()),
        ]);
    }
}
