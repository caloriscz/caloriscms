<?php
declare(strict_types=1);

namespace App\Router;

use App\SlugRouter;
use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;
use Model;
use Nette\Database\Context;
use Symfony\Component\Translation\Translator;

class RouterFactory
{
    /** @var Model\SlugManager */
    private static $slugManager;

    /** @var Context */
    private $database;

    /** @var Translator @inject */
    public $translator;

    public static function createRouter(Context $database, \Contributte\Translation\Translator $translator): RouteList
    {
        $router = new RouteList;


        $router->addRoute('api/<presenter>/<action>/<id>', [
            'module' => 'Api',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => null
        ]);

        $router->addRoute('sitemap.xml', [
            'module' => 'Api',
            'presenter' => 'Sitemap',
            'action' => 'default',
            'id' => null
        ]);

        $router->addRoute('admin/[<locale=cs cs|en>/]<presenter>/<action>/<id>', [
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => null
        ]);

        /** Homepage won't work without this router */
        $router->addRoute('[<locale=cs cs|en>/]', [
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => null
        ]);

        $slugRouter = new SlugRouter(new Model\SlugManager($database, $translator));

        $router->add($slugRouter);

        $router->addRoute('[<locale=cs cs|en>/]<presenter>/<action>/<id>', [
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => null
        ]);

        return $router;
    }

}