<?php

use Nette\Application\UI\Control;

/**
 * List of languages for administration
 * Class LangSelectorControl
 */
class LangSelectorControl extends Control
{

    /** @var \Kdyby\Doctrine\EntityManager @inject */
    public $em;

    public function __construct(\Kdyby\Doctrine\EntityManager $em)
    {
        $this->em = $em;
    }

    public function render()
    {
        $this->template->id = $this->getPresenter()->getParameter('id');
        $this->template->languages = $this->em->getRepository(\App\Model\Entity\Languages::class)->findAll();

        $this->template->langSelected = $this->getPresenter()->getParameter('l');
        $this->template->arr = $this->getPresenter()->getParameters();

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->setFile(__DIR__ . '/LangSelectorControl.latte');
        $this->template->render();
    }

}
