<?php
declare(strict_types=1);

namespace App\ApiModule\Presenters;

use App\Model\Api\ApiTokenAuthenticator;
use Nette\Application\Responses\TextResponse;
use Nette\Database\Explorer;
use Tracy\Debugger;

class FeedPresenter extends BasePresenter
{
    public function __construct(Explorer $database, ApiTokenAuthenticator $apiTokenAuthenticator)
    {
        parent::__construct($database, $apiTokenAuthenticator);
    }

    public function renderDefault(): void
    {
        Debugger::$showBar = false;

        $settings = $this->template->settings;

        if (($settings['rss:enabled'] ?? '0') !== '1') {
            $this->getHttpResponse()->setCode(404);
            $this->getHttpResponse()->setContentType('text/plain', 'utf-8');
            $this->sendResponse(new TextResponse('RSS feed is disabled'));
        }

        $limit = (int) ($settings['rss:limit'] ?? 20);
        $limit = max(1, min(100, $limit));

        $baseUrl = rtrim((string) ($settings['site:url:base'] ?? ''), '/');

        if ($baseUrl === '') {
            $baseUrl = rtrim($this->getHttpRequest()->getUrl()->getBaseUrl(), '/');
        }

        $feedUrl = $baseUrl . '/rss.xml';

        $this->template->feedTitle = $settings['rss:title'] ?: (($settings['site:title'] ?? 'Caloris CMS') . ' RSS');
        $this->template->feedDescription = $settings['rss:description'] ?: '';
        $this->template->feedUrl = $feedUrl;
        $this->template->baseUrl = $baseUrl;
        $this->template->buildDate = date(DATE_RSS);
        $this->template->pages = $this->database->table('pages')
            ->where('public', 1)
            ->where('pages_types_id', 2)
            ->where('date_published IS NULL OR date_published <= ?', date('Y-m-d H:i:s'))
            ->order('COALESCE(date_published, date_created) DESC, id DESC')
            ->limit($limit);

        $this->getHttpResponse()->setContentType('application/rss+xml', 'utf-8');
    }
}
