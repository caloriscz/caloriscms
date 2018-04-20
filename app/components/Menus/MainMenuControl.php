<?php

namespace Caloriscz\Menus\Admin;

use App\Model\Entity\PagesTypes;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

class MainMenuControl extends Control
{
    /** @var EntityManager @inject */
    public $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function render()
    {
        if (isset($this->getPresenter()->template->member)) {
            $this->template->member = $this->getPresenter()->template->member;
        }

        $this->template->settings = $this->getPresenter()->template->settings;
        $pageTypes = $this->em->getRepository(PagesTypes::class);
        $this->template->pageTypes = $pageTypes->findAll();

        $this->template->setFile(__DIR__ . '/MainMenuControl.latte');
        $this->template->render();
    }

}
