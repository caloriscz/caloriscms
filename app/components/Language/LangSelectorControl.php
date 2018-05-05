<?php

use App\Model\Entity\Languages;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * List of languages for administration
 * Class LangSelectorControl
 */
class LangSelectorControl extends Control
{

    /** @var EntityManager @inject */
    public $em;

    /**
     * LangSelectorControl constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function render(): void
    {
        $this->template->id = $this->getPresenter()->getParameter('id');
        $this->template->languages = $this->em->getRepository(Languages::class)->findAll();

        $this->template->langSelected = $this->getPresenter()->getParameter('l');
        $this->template->arr = $this->getPresenter()->getParameters();

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->setFile(__DIR__ . '/LangSelectorControl.latte');
        $this->template->render();
    }

}
