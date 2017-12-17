<?php

namespace Caloriscz\Menus\Admin;

use App\Model\Entity\Addons;
use App\Model\Entity\PagesTypes;
use Nette\Application\UI\Control;

class MainMenuControl extends Control
{
    /** @var \Kdyby\Doctrine\EntityManager @inject */
    public $em;

    public function __construct(\Kdyby\Doctrine\EntityManager $em)
    {
        $this->em = $em;
    }

    public function render()
    {
        if (isset($this->presenter->template->member)) {
            $this->template->member = $this->presenter->template->member;
        }

        $this->template->settings = $this->presenter->template->settings;

        $pageTypes = $this->em->getRepository(PagesTypes::class);
        $this->template->pageTypes = $pageTypes->findAll();

        $links = $this->em->getRepository(Addons::class);
        $this->template->addons = $links->findBy(['active' => 1]);

        $this->template->setFile(__DIR__ . '/MainMenuControl.latte');

        $this->template->render();
    }

}
