<?php

namespace Caloriscz\Appearance;

use App\Model\Entity\Carousel;
use App\Model\IO;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Tracy\Debugger;

class CarouselManagerControl extends Control
{

    /** @var EntityManager @inject */
    public $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Exception
     */
    public function handleImages()
    {
        $updateSorter = $this->em->getConnection()->prepare('SET @i = 1000;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');
        $updateSorter->execute();
        $updateSorter->closeCursor();

        try {
            $arrSorted = explode(',', $this->presenter->getParameter('sortable'));
            $arrIds = explode(',', $this->presenter->getParameter('ids'));

            for ($a = 0; $a < count($arrSorted); $a++) {
                $this->em->getReference(Carousel::class, $arrSorted[$a])->setSorted($a);
            }

            $this->em->flush();
        } catch (ORMException $e) {
            exit();
        }

        exit();
    }

    public function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $carousel = $this->em->find(Carousel::class, $id);
            $image = $carousel->getImage();

            $this->em->remove($carousel);

            IO::remove(APP_DIR . '/images/carousel/' . $image);
            unset($image);
            $this->em->flush();
        }

        $this->redirect('this', ['id' => null]);
    }

    public function render()
    {
        $this->template->carousel = $this->em->getRepository(Carousel::class)->findBy([], ['sorted' => 'ASC']);
        $this->template->setFile(__DIR__ . '/CarouselManagerControl.latte');
        $this->template->render();
    }

}
