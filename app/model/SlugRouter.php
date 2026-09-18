<?php

namespace App;

use Model\SlugManager;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\UrlScript;


class SlugRouter implements \Nette\Routing\Router
{
    private SlugManager $slugManager;

    public function __construct(SlugManager $slugManager)
    {
        $this->slugManager = $slugManager;
    }

    /**
     * Maps HTTP request to a Request object.
     * @param HttpRequest $httpRequest
     */
    public function match(HttpRequest $httpRequest): ?array
    {
        $url = $httpRequest->getUrl();
        $path = trim($url->getRelativePath(), '/');
        $parts = $path === '' ? [] : explode('/', $path);
        $params = [];
        $lang = null;

        if ($parts && in_array($parts[0], $this->slugManager->getLocale(), true)) {
            $lang = array_shift($parts);
            $params['locale'] = $lang;
        }

        $params['prefix'] = null;
        if (!$parts) {
            $row = $this->slugManager->getDefault();
        } elseif (count($parts) === 1) {
            $row = $this->slugManager->getRowBySlug($parts[0], $lang);
        } elseif (count($parts) <= 3) {
            // Prefer the existing prefix/slug shape over the slug/id variant.
            $row = $this->slugManager->getRowBySlug($parts[1], $lang, $parts[0]);
            if ($row) {
                $params['prefix'] = $parts[0];
                if (isset($parts[2])) {
                    $params['id'] = $parts[2];
                }
            } elseif (count($parts) === 2) {
                $row = $this->slugManager->getRowBySlug($parts[0], $lang);
                $params['id'] = $parts[1];
            }
        } else {
            return null;
        }

        if (!$row) {
            return null;
        }

        $params['page_id'] = $row->id;

        // Nette decodes query values, bare keys and arrays with PHP query semantics.
        // Query input must not replace the route selected from the CMS path.
        $query = $httpRequest->getQuery();
        // The old nested result did not dispatch query-string signals. Keep that
        // boundary: legacy frontend write signals need their own security review.
        unset($query['presenter'], $query['module'], $query['action'], $query['page_id'], $query['slug'],
            $query['locale'], $query['prefix'], $query['method'], $query['do']);
        $params += $query;

        $pageType = $row->ref('pages_types', 'pages_types_id');
        $pageTemplate = null;

        if ($row->pages_templates_id !== null) {
            $pageTemplate = $row->ref('pages_templates', 'pages_templates_id');
        } elseif ($pageType && $pageType->pages_templates_id !== null) {
            $pageTemplate = $pageType->ref('pages_templates', 'pages_templates_id');
        }

        if ($pageTemplate) {

            $templateInfo = explode(':', $pageTemplate->template);

            if (count($templateInfo) !== 3) {
                return null;
            }

            $presenter = $templateInfo[0] . ':' . $templateInfo[1];
            $params['action'] = $templateInfo[2];
        } else {
            if (!$pageType) {
                return null;
            }

            $presenter = $pageType->presenter;

            if ($row->pages_types_id === 9) {
                $params['action'] = substr($row->presenter, strrpos($row->presenter, ":") + 1);
            } else {
                $params['action'] = $pageType->action;
            }
        }

        $params['presenter'] = $presenter;
        $params['method'] = $httpRequest->getMethod();
        return $params;

    }

    /**
     * Constructs an absolute URL from route parameters.
     */
    public function constructUrl(array $param, UrlScript $refUrl): ?string
    {
        $params = $param;

        $query = $params;
        unset($query['presenter'], $query['method'], $query['action'], $query['page_id'],
            $query['slug'], $query['id'], $query['locale'], $query['prefix']);

        if (isset($params['slug'])) {
            $slug = strtolower($params['slug']);
        } else {
            if (isset($params['page_id'])) {
                $row = $this->slugManager->getSlugById($params['page_id']);

                if ($row) {
                    if (isset($params['locale']) && $params['locale'] !== 'cs') {
                        $slug = $row->{'slug_' . $params['locale']};
                    } else {
                        $slug = $row->{'slug'};
                    }

                } else {
                    return NULL;
                }
            } else {
                return NULL;
            }
        }

        if (isset($params['locale'])) {
            $locale = $params['locale'] . '/';
        } else {
            $locale = null;
        }

        if (isset($params['prefix'])) {
            $prefix = $params['prefix'] . '/';
        } else {
            $prefix = null;
        }
        $url = $refUrl->getBaseUrl() . $locale . $prefix . $slug;

        if (isset($params['id'])) {
            $url .= '/' . rawurlencode((string) $params['id']);
        } elseif (isset($params['action']) && $params['action'] !== 'default') {
            $url .= '/';
        }

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        return $url;
    }
}
