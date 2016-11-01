<?php

namespace App;

use App\Model\SlugManager;
use Nette,
    Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route,
    Nette\Application\Routers\SimpleRouter;
use Model;

/**
 * Router factory.
 */
class RouterFactory
{
    /** @var SlugManager */
    private $SlugManager;

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database, Model\SlugManager $SlugManager)
    {
        $this->database = $database;
        $this->SlugManager = $SlugManager;
    }


    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();


        $router[] = new Route('api/<presenter>/<action>/<id>', array(
            'module' => 'Api',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));

        $router[] = new Route('sitemap.xml', array(
            'module' => 'Api',
            'presenter' => 'Sitemap',
            'action' => 'default',
            'id' => NULL,
        ));

        $router[] = new Route('admin/[<locale=cs cs|en>/]<presenter>/<action>/<id>', array(
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));

        $router[] = new SlugRouter($this->SlugManager);

        $router[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>/<id>', array(
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));

        return $router;
    }

}