<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Appearance\CarouselGridControl;
use Caloriscz\Appearance\EditCarouselControl;
use Caloriscz\Appearance\SavePathsControl;
use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class AppearancePresenter extends BasePresenter
{

    protected function createComponentEditCarousel()
    {
        return new EditCarouselControl($this->database);
    }

    protected function createComponentSavePaths()
    {
        return new SavePathsControl($this->database);
    }

    protected function createComponentCarouselGrid()
    {
        return new CarouselGridControl($this->database);
    }

    /**
     * Sorting
     */
    public function handleSort()
    {
        if ($this->getParameter('prev_id')) {
            $prev = $this->database->table('carousel')->get($this->getParameter('prev_id'));

            $this->database->table('carousel')->where(array("id" => $this->getParameter('item_id')))->update(array('sorted' => ($prev->sorted + 1)));
        } else {
            $next = $this->database->table('carousel')->get($this->getParameter('next_id'));

            $this->database->table('carousel')->where(array("id" => $this->getParameter('item_id')))->update(array('sorted' => ($next->sorted - 1)));
        }

        $this->database->query('SET @i = 1;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');

        $this->redirect(':Admin:Appearance:carousel', array('id' => null));
    }

    /*
     * Insert new
     */
    public function handleInsert()
    {
        $carousel = $this->database->table('carousel')->insert(array());

        $this->database->query('SET @i = 1;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');

        $this->redirect(":Admin:Appearance:carouselDetail", array("id" => $carousel));
    }

    public function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {

            $product = $this->database->table('carousel')->get($id[$a]);
            $image = $product->image;
            $product->delete();

            Model\IO::remove(APP_DIR . '/images/carousel/' . $image);
            unset($image);
        }

        $this->redirect(":Admin:Appearance:carousel", array("id" => null));
    }

    public function renderDefault()
    {
        $arr = [
            'settings_categories_id' => 20,
            'type' => 'local_path',
        ];

        $this->template->settingsDb = $this->database->table('settings')->where($arr);
    }

}
