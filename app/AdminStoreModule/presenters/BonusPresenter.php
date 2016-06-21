<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Orders presenter.
 */
class BonusPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    protected function createComponentBonusGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);
        $grid->setDataSource($this->database->table("store_bonus"));

        $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];

        $grid->addColumnLink('id', 'Výrobek')
            ->setRenderer(function ($item) {
                $url = Nette\Utils\Html::el('a')->href($this->link('detail', array("id" => $item->id)))
                    ->setText($item->stock->pages->title . ' (' . $item->stock->title . ')');
                return $url;
            })
            ->setSortable();
        $grid->addColumnText('from', 'Od částky')
            ->setSortable();

        $grid->setTranslator($this->translator);
    }

    /**
     * Insert bonus
     */
    protected function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("parent_id");
        $form->addHidden("type");
        $form->addText('product', 'Zadejte číslo bonusu')
            ->setAttribute("class", "form-control")
            ->setOption("description", "Najdete jej v zásobách výrobku jako č.");
        $form->addText('from', 'od částky')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    public function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_bonus")->insert(array(
            "stock_id" => $form->values->product,
            "from" => $form->values->from,
        ));

        $this->redirect(":AdminStore:Bonus:default", array("id" => null));
    }

    public function renderDefault()
    {
        $this->template->settingsDb = $this->database
            ->table("store_bonus");
    }

}
