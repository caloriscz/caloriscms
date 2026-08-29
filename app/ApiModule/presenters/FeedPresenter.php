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

        $groupSlug = $this->getHttpRequest()->getQuery('group');
        $groupTitle = null;
        $feedParentId = null;

        if ($groupSlug !== null && $groupSlug !== '') {
            $groupPage = $this->database->table('pages')->where('slug', $groupSlug)->fetch();

            if (!$groupPage) {
                $this->getHttpResponse()->setCode(404);
                $this->getHttpResponse()->setContentType('text/plain', 'utf-8');
                $this->sendResponse(new TextResponse('RSS group was not found'));
            }

            $feedParentId = (int) $groupPage->id;
            $groupTitle = (string) $groupPage->title;
        } else {
            $blogPageType = $this->database->table('pages_types')->get(2);
            $feedParentId = $blogPageType && $blogPageType->pages_id !== null ? (int) $blogPageType->pages_id : null;
        }

        $feedUrl = $baseUrl . '/rss.xml';

        if ($groupSlug !== null && $groupSlug !== '') {
            $feedUrl .= '?group=' . rawurlencode((string) $groupSlug);
        }

        $pages = $this->database->table('pages')
            ->where('public', 1)
            ->where('pages_types_id', 2)
            ->where('date_published IS NULL OR date_published <= ?', date('Y-m-d H:i:s'));

        if ($feedParentId !== null) {
            $pages->where('pages_id', $feedParentId);
        }

        $feedTitle = $settings['rss:title'] ?: (($settings['site:title'] ?? 'Caloris CMS') . ' RSS');

        if ($groupTitle !== null && $groupTitle !== '') {
            $feedTitle .= ' - ' . $groupTitle;
        }

        $this->template->feedTitle = $feedTitle;
        $this->template->feedDescription = $settings['rss:description'] ?: '';
        $this->template->feedUrl = $feedUrl;
        $this->template->baseUrl = $baseUrl;
        $this->template->buildDate = date(DATE_RSS);
        $this->template->pages = $pages
            ->order('COALESCE(date_published, date_created) DESC, id DESC')
            ->limit($limit);

        $this->getHttpResponse()->setContentType('text/xml', 'utf-8');
    }
}
