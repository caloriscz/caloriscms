<?php

namespace Caloriscz\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class CarouselManagerControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Exception
     */
    public function handleImages(): void
    {
        $updateSorter = $this->database->query('SET @i = 1000;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');
        exit();
    }

    public function handleDelete($id): void
    {
        for ($a = 0, $aMax = count($id); $a < $aMax; $a++) {
            $carousel = $this->database->table('carousel')->get($id);
            $image = $carousel->image;

            $this->database->table($carousel)->get($id)->delete();

            IO::remove(APP_DIR . '/images/carousel/' . $image);
            unset($image);
        }

        $this->redirect('this', ['id' => null]);
    }

    public function render(): void
    {
        $this->template->carousel = $this->database->table('carousel')->order('sortedASC');
        $this->template->setFile(__DIR__ . '/CarouselManagerControl.latte');
        $this->template->render();
    }

}
