<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class AppearancePresenter extends BasePresenter
{

    protected function createComponentEditCarousel()
    {
        $control = new \Caloriscz\Appearance\EditCarouselControl($this->database);
        return $control;
    }

    protected function createComponentSavePaths()
    {
        $control = new \Caloriscz\Appearance\SavePathsControl($this->database);
        return $control;
    }

    protected function createComponentCarouselGrid()
    {
        $control = new \Caloriscz\Appearance\CarouselGridControl($this->database);
        return $control;
    }

    /**
     * Sorting
     */
    function handleSort()
    {
        if ($this->getParameter('prev_id')) {
            $prev = $this->database->table("carousel")->get($this->getParameter('prev_id'));

            $this->database->table("carousel")->where(array("id" => $this->getParameter('item_id')))->update(array("sorted" => ($prev->sorted + 1)));
        } else {
            $next = $this->database->table("carousel")->get($this->getParameter('next_id'));

            $this->database->table("carousel")->where(array("id" => $this->getParameter('item_id')))->update(array("sorted" => ($next->sorted - 1)));
        }

        $this->database->query("SET @i = 1;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC");

        $this->redirect(":Admin:Appearance:carousel", array("id" => null));
    }

    /*
     * Insert new
     */
    function handleInsert()
    {
        $carousel = $this->database->table("carousel")->insert(array());

        $this->database->query("SET @i = 1;UPDATE `carousel` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC");

        $this->redirect(":Admin:Appearance:carouselDetail", array("id" => $carousel));
    }

    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {

            $product = $this->database->table("carousel")->get($id[$a]);
            $image = $product->image;
            $product->delete();

            Model\IO::remove(APP_DIR . '/images/carousel/' . $image);
            unset($image);
        }

        $this->redirect(":Admin:Appearance:carousel", array("id" => null));
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:settings'];

        $arr = array(
            "categories_id" => 20,
            "type" => "local_path",
        );

        $this->template->settingsDb = $this->database->table("settings")
            ->where($arr);
    }

}
