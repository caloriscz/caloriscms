<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Store parametres presenter.
 */
class ParamsPresenter extends BasePresenter
{

    /**
     * Category edit
     */
    function createComponentEditParamForm()
    {
        $this->template->param = $this->database->table("param")->get($this->getParameter('id'));

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addText("param_cs", "Parametr");
        $form->addText("param_en", "Parametr (angličtina)");
        $form->addText("prefix", "Před textem");
        $form->addText("suffix", "Za textem");
        $form->addSubmit("submitm", "dictionary.main.Save")
                ->setAttribute("class", "btn btn-success");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "param_cs" => $this->template->param->param_cs,
            "param_en" => $this->template->param->param_en,
            "prefix" => $this->template->param->prefix,
            "suffix" => $this->template->param->suffix,
        ));

        $form->onSuccess[] = $this->editParamFormSucceeded;
        return $form;
    }

    function editParamFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("param")->get($form->values->id)
                ->update(array(
                    "param_cs" => $form->values->param_cs,
                    "param_en" => $form->values->param_en,
                    "prefix" => $form->values->prefix,
                    "suffix" => $form->values->suffix,
        ));

        $this->redirect(':AdminStore:Params:detail', array("id" => $form->values->id));
    }

    /**
     * Category edit
     */
    function createComponentEditCategoryForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->editCategoryFormSucceeded;
        return $form;
    }

    function editCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT, 'catg[]'); // get value from html input

        $this->database->table("param_categories")->where(array("param_id" => $form->values->id))->delete();

        foreach ($values as $value) {
            $this->database->table("store_param_categories")->insert(array(
                "store_param_id" => $form->values->id,
                "categories_id" => $value,
            ));
        }

        $this->redirect(':AdminStore:Params:detail', array("id" => $form->values->id));
    }

    /**
     * Insert parameter
     */
    protected function createComponentInsertNewParamForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText('param', 'dictionary.main.Title');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertNewParamFormSucceeded;
        return $form;
    }

    public function insertNewParamFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_param")->insert(array(
            "param" => $form->values->param,
        ));

        $this->redirect(":AdminStore:Catalogue:parametres", array("id" => null));
    }

    /**
     * Delete parameter communication
     */
    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $this->database->table("param")->get($id[$a])->delete();
        }

        $this->redirect(":AdminStore:Params:default", array("id" => NULL));
    }

    protected function createComponentParamsGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        $params = $this->database->table("store_param");

        $grid->setDataSource($params);
        $grid->addGroupAction($this->translator->translate('dictionary.main.Delete'))->onSelect[] = [$this, 'handleDelete'];

        $grid->addColumnLink('param_cs', 'dictionary.main.Parametres')
                ->setRenderer(function($item) {
                    $url = Nette\Utils\Html::el('a')->href($this->link('detail', array("id" => $item->id)))
                            ->setText($item->param_cs);
                    return $url;
                })
                ->setSortable();
        $grid->addFilterText('param_cs', 'Název');

        $grid->setTranslator($this->translator);
    }

    function renderDetail()
    {
        if ($this->getParameter('id')) {
            $this->template->param = $this->database->table("param")->get($this->getParameter('id'));
        }

        $categoriesSelected = $this->database->table("param_categories")->where(array(
            'param_id' => $this->getParameter("id"),
        ));

        foreach ($categoriesSelected as $arr) {
            $categories[] = $arr->categories_id;
        }

        $this->template->categories = $categories;
    }

}
