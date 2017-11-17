<?php

namespace Caloriscz\Menus\Admin;

use App\Model\Entity\Addons;
use Nette\Application\UI\Control;

class MainMenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    /** @var \Kdyby\Doctrine\EntityManager @inject */
    public $em;

    public function __construct(\Nette\Database\Context $database, \Kdyby\Doctrine\EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;
    }


    public function render()
    {
        $template = $this->template;

        if (isset($this->presenter->template->member)) {
            $template->member = $this->presenter->template->member;
        }

        $template->settings = $this->presenter->template->settings;
        $template->database = $this->database;

        $template->pagesTypes = $this->database->table("pages_types");

        $links = $this->em->getRepository(Addons::class);
        $template->addons = $links->findBy(["active" => 1]);

        $template->setFile(__DIR__ . '/MainMenuControl.latte');

        $template->render();
    }

}
