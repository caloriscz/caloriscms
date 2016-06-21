<?php
use Nette\Application\Request;

class FilterRoute extends Nette\Application\Routers\Route
{

    const WAY_IN = 'in';
    const WAY_OUT = 'out';

// překlad a vyhledání v databázi
    /** @var array */
    private $filters = array();

    /**
     * @param Nette\Web\IHttpRequest $httpRequest
     * @return Nette\Application\Request|NULL
     */
    public function match(Nette\Http\IRequest $httpRequest)
    {
        $appRequest = parent::match($httpRequest);
        if (!$appRequest) {
            return $appRequest;
        }

        if ($params = $this->doFilterParams($this->getRequestParams($appRequest), $appRequest, self::WAY_IN)) {
            return $this->setRequestParams($appRequest, $params);
        }

        return NULL;
    }

    /**
     * @param Nette\Application\Request $appRequest
     * @param Nette\Web\Uri $refUri
     * @return string
     */
    public function constructUrl(Request $appRequest, Nette\Http\Url $refUrl)
    {
        if ($params = $this->doFilterParams($this->getRequestParams($appRequest), $appRequest, self::WAY_OUT)) {
            $appRequest = $this->setRequestParams($appRequest, $params);
            return parent::constructUrl($appRequest, $refUrl);
        }

        return NULL;
    }

    /**
     * @param string $param
     * @param callable $in
     * @param callable $out
     * @return SmarterRoute
     */
    public function addFilter($param, $in, $out = NULL)
    {
        $this->filters[$param] = array(
            self::WAY_IN => callback($in),
            self::WAY_OUT => $out ? callback($out) : NULL
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Nette\Application\Request $appRequest
     * @return array
     */
    private function getRequestParams(Request $appRequest)
    {
        $params = $appRequest->getParameters();
        $metadata = $this->getDefaults();

        $presenter = $appRequest->getPresenterName();
        $params[self::PRESENTER_KEY] = $presenter;

        if (isset($metadata[self::MODULE_KEY])) { // try split into module and [submodule:]presenter parts
            $module = $metadata[self::MODULE_KEY];
            if (isset($module['fixity']) && strncasecmp($presenter, $module[self::VALUE] . ':', strlen($module[self::VALUE]) + 1) === 0) {
                $a = strlen($module[self::VALUE]);
            } else {
                $a = strrpos($presenter, ':');
            }

            if ($a === FALSE) {
                $params[self::MODULE_KEY] = '';
            } else {
                $params[self::MODULE_KEY] = substr($presenter, 0, $a);
                $params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
            }
        }

        return $params;
    }

    /**
     * @param Nette\Application\Request $appRequest
     * @param array $params
     * @return Nette\Application\Request
     */
    private function setRequestParams(Request $appRequest, array $params)
    {
        $metadata = $this->getDefaults();

        if (!isset($params[self::PRESENTER_KEY])) {
            throw new \InvalidStateException('Missing presenter in route definition.');
        }
        if (isset($metadata[self::MODULE_KEY])) {
            if (!isset($params[self::MODULE_KEY])) {
                throw new \InvalidStateException('Missing module in route definition.');
            }
            $presenter = $params[self::MODULE_KEY] . ':' . $params[self::PRESENTER_KEY];
            unset($params[self::MODULE_KEY], $params[self::PRESENTER_KEY]);
        } else {
            $presenter = $params[self::PRESENTER_KEY];
            unset($params[self::PRESENTER_KEY]);
        }

        $appRequest->setPresenterName($presenter);
        $appRequest->setParameters($params);

        return $appRequest;
    }

    /**
     * @param array $params
     * @param Nette\Application\Request $request
     * @param string $way
     */
    private function doFilterParams($params, Request $request, $way)
    {
// tady mám k dispozici všechny parametry
        foreach ($this->getFilters() as $param => $filters) {
            if (!isset($params[$param]) || !isset($filters[$way])) {
                continue; // param not found
            }

            $params[$param] = call_user_func($filters[$way], (string)$params[$param], $request);
            if ($params[$param] === NULL) {
                return NULL; // rejected by filter
            }
        }

        return $params;
    }

}