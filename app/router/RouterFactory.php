<?php

namespace App;

use App\Model\SlugManager;
use Nette,
    Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;
use Model;

/**
 * Router factory.
 */
class RouterFactory
{
    /** @var slugManager */
    private $slugManager;

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(Nette\Database\Context $database, Model\SlugManager $slugManager)
    {
        $this->database = $database;
        $this->slugManager = $slugManager;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();


        $router[] = new Route('api/<presenter>/<action>/<id>', [
            'module' => 'Api',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL
        ]);

        $router[] = new Route('sitemap.xml', [
            'module' => 'Api',
            'presenter' => 'Sitemap',
            'action' => 'default',
            'id' => NULL
        ]);

        $router[] = new Route('admin/[<locale=cs cs|en>/]<presenter>/<action>/<id>', [
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL
        ]);

        /** Homepage won't work without this router */
        $router[] = new Route('[<locale=cs cs|en>/]', [
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL
        ]);

        $router[] = new SlugRouter($this->slugManager);

        $router[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>/<id>', [
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL
        ]);

        return $router;
    }

}