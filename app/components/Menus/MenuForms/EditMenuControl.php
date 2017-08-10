<?php
namespace Caloriscz\Menus\MenuForms;

use Nette\Application\UI\Control;

class EditMenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit category
     */
    protected function createComponentEditForm()
    {
        $pages = new \App\Model\Page($this->database);
        $categoryAll = new \App\Model\Menu($this->database);
        $category = $this->database->table("menu")->get($this->presenter->getParameter("id"));
        $categories = $categoryAll->getAll();
        unset($categories[$category->id]);

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextarea('description', 'dictionary.main.Description')
            ->setAttribute("class", "form-control");
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('admin.categories.NothingRelated')
            ->setAttribute("class", "form-control");
        $form->addSelect('page', 'admin.categories.SelectPage', $pages->getPageList())
            ->setPrompt('admin.categories.PageSelectedManually')
            ->setAttribute("class", "form-control");
        $form->addText('url', 'dictionary.main.URL');
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $arr = array(
            "id" => $category->id,
            "title" => $category->title,
            "description" => $category->description,
            "page" => $category->pages_id,
            "parent" => $category->parent_id,
            "url" => $category->url,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("menu")->get($form->values->id)
            ->update(array(
                "title" => $form->values->title,
                "description" => $form->values->description,
                "pages_id" => $form->values->page,
                "parent_id" => $form->values->parent,
                "url" => $form->values->url,
            ));

        $this->presenter->redirect(this, array("id" => $form->values->id));
    }

    public function render()
    {
        $template = $this->template;
        $template->languages = $this->database->table("languages")->where("default", null);
        $template->setFile(__DIR__ . '/EditMenuControl.latte');

        $template->render();
    }

}
