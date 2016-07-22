<?php
namespace App;

use Model;
use Nette\Application\Routers;
use Nette\Application as App;
use Nette;
use Nette\Http;
use Symfony\Component\Translation\Translator;


class SlugRouter extends Nette\Object implements Nette\Application\IRouter
{

    /** @var SlugManager */
    private $slugManager;

    public function __construct(Model\SlugManager $slugManager)
    {
        $this->slugManager = $slugManager;
    }

    /**
     * Maps HTTP request to a Request object.
     *
     * @param \Nette\Http\IRequest $httpRequest
     * @throws \Nette\Application\BadRequestException
     * @return App\Request|NULL
     */
    public function match(Http\IRequest $httpRequest)
    {
        // 1) PARSE URL
        $url = $httpRequest->getUrl();
        $path = trim($url->path, $url->scriptPath);
        $params = array();
        $lang = array();


        if ($path !== '') {
            $parts = explode($url->scriptPath, $path, 4);

            if (in_array($parts[0], $this->slugManager->getLocale())) {
                $params['locale'] = $parts[0];
                $lang = $parts[0];
                unset($parts[0]);
                $parts = array_values($parts);
                $slugName = $parts[0];
            } else {
                $slugName = $parts[0];
            }



            //get row by slug
            $row = $this->slugManager->getRowBySlug($slugName, $lang);
        } else {
            $parts = array('Homepage', 'default');
            $row = $this->slugManager->getDefault();
        }


        if (!$row) {
            //throw new Nette\Application\BadRequestException('Page does not exist');
            return null;
        }


        //action
        if (isset($parts[1])) {
            $action = $parts[1];
        } else {
            $action = 'default';
        }

        //id
        if (isset($parts[2])) {
            $id = $parts[2];
        }

        //params

        $params['action'] = $action;
        $params['page_id'] = $row->id;
        //$params['locale'] = 'en';
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


        // 3) GET PRESENTER
        if ($row->pages_types_id == 0) {
            $presenter = $row->presenter;
        } else {
            $presenter = $row->pages_types->presenter;
            $params['action'] = $row->pages_types->action;
        }

//        echo $presenter;
        //      exit();

        return new App\Request($presenter, $httpRequest->getMethod(), $params, $httpRequest->getPost(), $httpRequest->getFiles(), array(App\Request::SECURED => $httpRequest->isSecured()));

    }

    /**
     * Constructs absolute URL from Request object.
     *
     * @return string|NULL
     */
    public function constructUrl(App\Request $appRequest, Http\Url $refUrl)
    {
        $params = $appRequest->getParameters();

        $query = $params;
        unset($query['action'], $query['page_id'], $query['slug'], $query['id'], $query['locale']);

        if (isset($params['slug'])) {
            $slug = strtolower($params['slug']);
        } else {
            if (isset($params['page_id'])) {
                $row = $this->slugManager->getSlugById($params['page_id']);

                if (isset($params['locale']) == 'cs') {
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

        $url = $refUrl->getScheme() . '://' . $refUrl->getHost() . $refUrl->getPath() . $locale . $slug;
        $params = $appRequest->getParameters();

        if (isset($params['action']) && $params['action'] !== 'default') {
            $url .= $refUrl->getPath();
        }

        if (isset($params['id'])) {
            if ($params['action'] == 'default' && isset($params['action'])) {
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