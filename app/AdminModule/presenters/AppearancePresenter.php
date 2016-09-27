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

    /**
     * Settings save
     */
    function createComponentEditSettingsForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('path_id');
        $form->addUpload("path", "dictionary.main.Image");
        $form->addSubmit('send', 'dictionary.main.Save');

        $form->onSuccess[] = $this->editSettingsSucceeded;
        return $form;
    }

    function editSettingsSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->path->name) < 1) {
            $this->redirect(":Admin:Appearance:default", array("id" => null));
        }

        if (file_exists($_FILES['path']['tmp_name']) || is_uploaded_file($_FILES['path']['tmp_name'])) {

            copy($_FILES['path']['tmp_name'], APP_DIR . '/images/paths/' . $form->values->path->name);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);
            chmod(APP_DIR . '/images/paths/' . $form->values->path->name, 0644);

            if (file_exists(APP_DIR . '/www/images/paths/' . $form->values->path->name)) {
                Model\IO::remove(APP_DIR . '/www/images/paths/' . $form->values->path->name);
            }

            $this->database->table("settings")->get($form->values->path_id)->update(array(
                "setvalue" => $form->values->path->name,
            ));

        }

        $this->redirect(":Admin:Appearance:default", array("id" => null));
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

        $grid->setTranslator($this->translator);
    }

}
