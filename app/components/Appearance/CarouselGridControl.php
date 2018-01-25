<?php

namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class CarouselGridControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    public function createComponentCarouselGrid($name)
    {
        $grid = new DataGrid($this, $name);


        $test = $this->database->table('carousel')->order('sorted');
        try {
            $grid->setDataSource($test);
            $grid->setSortable(true);
            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];

            $grid->addColumnText('title', 'dictionary.main.Title')
                ->setRenderer(function ($item) {
                    if ($item->title === '') {
                        $title = Html::el('a')->href('/admin/appearance/carousel-detail/' . $item->id)->setText('- nemá název - ');
                    } else {
                        $title = Html::el('a')->href('/admin/appearance/carousel-detail/' . $item->id)->setText($item->title);
                    }

                    return $title;
                });


            $grid->addColumnText('test', 'dictionary.main.Image')
                ->setRenderer(function ($item) {

                    if ($item->image ==='') {
                        $fileImage = '';
                    } else {
                        $fileImage = Html::el('img', array('style' => 'max-height: 130px;'))
                            ->src('/images/carousel/' . $item->image);
                    }

                    return $fileImage;
                });
        } catch (\Exception $e) {
            echo 'Grid error';
        }
        $grid->setTranslator($this->presenter->translator);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/CarouselGridControl.latte');
        $this->template->render();
    }

}
