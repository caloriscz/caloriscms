<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;

class CarouselGridControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function createComponentCarouselGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);


        $test = $this->database->table("carousel")->order("sorted");

        $grid->setDataSource($test);
        $grid->setSortable(true);
        $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];

        $grid->addColumnText('title', 'dictionary.main.Title')
            ->setRenderer(function ($item) {
                if ($item->title == '') {
                    $title = \Nette\Utils\Html::el('a')->href('/admin/appearance/carousel-detail/' . $item->id)->setText('- nemá název - ');
                } else {
                    $title = \Nette\Utils\Html::el('a')->href('/admin/appearance/carousel-detail/' . $item->id)->setText($item->title);
                }

                return $title;
            });


        $grid->addColumnText('test', 'dictionary.main.Image')
            ->setRenderer(function ($item) {

                if ($item->image == '') {
                    $fileImage = '';
                } else {
                    $fileImage = \Nette\Utils\Html::el('img', array('style' => 'max-height: 130px;'))
                        ->src('/images/carousel/' . $item->image);
                }

                return $fileImage;
            });

        $grid->setTranslator($this->presenter->translator);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/CarouselGridControl.latte');

        $template->render();
    }

}
