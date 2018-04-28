<?php
namespace App;

use Model\SlugManager;
use Nette;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;


class SlugRouter implements Nette\Application\IRouter
{

    /** @var SlugManager */
    private $slugManager;

    public function __construct(SlugManager $slugManager)
    {
        $this->slugManager = $slugManager;
    }

    /**
     * Maps HTTP request to a Request object.
     * @param IRequest $httpRequest
     * @return Request|null
     */
    public function match(IRequest $httpRequest)
    {
        // 1) PARSE URL
        $url = $httpRequest->getUrl();
        $path = trim($url->path, $url->scriptPath);
        $params = [];
        $lang = null;

        if ($path !== '') {
            $parts = explode($url->scriptPath, $path, 4);

            if (\in_array($parts[0], $this->slugManager->getLocale(), true)) {
                $params['locale'] = $parts[0];
                $lang = $parts[0];
                unset($parts[0]);
                $parts = array_values($parts);

                if (\count($parts) === 2) {
                    $slugName = $parts[1];
                    $params['prefix'] = $parts[0];
                } else {
                    $slugName = $parts[0];
                }

            } else {
                if (\count($parts) === 2) {
                    $slugName = $parts[1];
                    $params['prefix'] = $parts[0];
                } else {
                    $slugName = $parts[0];
                    $params['prefix'] = null;
                }
            }

            //get row by slug
            $row = $this->slugManager->getRowBySlug($slugName, $lang, $params['prefix']);
        } else {
            $parts = ['Homepage', 'default'];
            $row = $this->slugManager->getDefault();
        }

        if (!$row) {
            return null;
        }

        if (isset($parts[2])) {
            $id = $parts[2];
        }

        $params['page_id'] = $row->id;
        if (isset($id)) {
            $params['id'] = $id;
        }

        //$url->query into params
        if ($url->getQuery() !== '') {
            $query = explode('&', $url->getQuery());
            foreach ($query as $singlequery) {
                $result = explode('=', $singlequery);
                $params[$result[0]] = $result[1];
            }
        }

        if ($row->pages_templates_id !== null) {
            $templateInfo = explode(':', $row->pages_templates->template);

            $presenter = $templateInfo[0] . ':' . $templateInfo[1];
            $params['action'] = $templateInfo[2];
        } else {
            $presenter = $row->pages_types->presenter;

            if ($row->pages_types_id === 9) {
                $params['action'] = substr($row->presenter, strrpos($row->presenter, ":") + 1);
            } else {
                $params['action'] = $row->pages_types->action;
            }
        }

        return new Request($presenter, $httpRequest->getMethod(), $params, $httpRequest->getPost(), $httpRequest->getFiles(), [Request::SECURED => $httpRequest->isSecured()]);

    }

    /**
     * Constructs absolute URL from Request object.
     *
     * @param Request $appRequest
     * @param Url $refUrl
     * @return string|NULL
     */
    public function constructUrl(Request $appRequest, Url $refUrl)
    {
        $params = $appRequest->getParameters();

        $query = $params;
        unset($query['action'], $query['page_id'], $query['slug'], $query['id'], $query['locale'], $query['prefix']);

        if (isset($params['slug'])) {
            $slug = strtolower($params['slug']);
        } else {
            if (isset($params['page_id'])) {
                $row = $this->slugManager->getSlugById($params['page_id']);

                // todo peekay Change cs for selected language

                if (isset($query['locale'])) {
                    unset($params['locale']);
                }

                if ($row) {
                    if (isset($params['locale'])) {
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
        $url = $refUrl->getScheme() . '://' . $refUrl->getHost() . $refUrl->getPath() . $locale . $prefix . $slug;
        $params = $appRequest->getParameters();

        if (isset($params['action']) && $params['action'] !== 'default') {
            $url .= $refUrl->getPath();
        }

        if (isset($params['id'])) {
            if ($params['action'] === 'default' && isset($params['action'])) {
                $url .= $refUrl->getPath();
            }
            $url .= $refUrl->getPath() . $params['id'];
        }

        if (count($query) > 0) {
            $queryString = '?';

            foreach ($query as $key => $parameter) {
                $queryString .= $key . '=' . $parameter . '&';
            }

            $finalQueryString = substr($queryString, 0, -1);

            $url .= $finalQueryString;
        }

        return $url;
    }
}