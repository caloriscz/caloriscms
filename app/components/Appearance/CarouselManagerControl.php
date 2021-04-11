<?php

namespace Caloriscz\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class CarouselManagerControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Resort images in order to enjoy sorting images from one :)
     * @throws \Exception
     */
    public function handleImages(): void
    {
        $sortableArray = array_map('intval', explode(',', $this->getPresenter()->getParameter('sortable')));

        $this->database->query('SET @i = 1000');
        $this->database->query('UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY FIELD(`id`, ?)', $sortableArray);
        exit();
    }

    public function handleDelete(int $id): void
    {
        $carousel = $this->database->table('carousel')->get($id);
        $image = $carousel->image ?? null;

        $this->database->table('carousel')->get($id)->delete();

        if ($image !== null) {
            IO::remove(APP_DIR . '/images/carousel/' . $image);
            unset($image);
        }

        $this->redirect('this', ['id' => null]);
    }

    public function render(): void
    {
        $this->template->carousel = $this->database->table('carousel')->order('sorted ASC');
        $this->template->setFile(__DIR__ . '/CarouselManagerControl.latte');
        $this->template->render();
    }

}
