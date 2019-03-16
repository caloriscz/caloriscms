<?php

namespace App\Forms\Contacts;

use App\Model\Category;
use App\Model\Slug;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertContactCategoryControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert contact category
     * @return BootstrapUIForm
     */
    protected function createComponentInsertCategoryForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('parent_id');
        $form->addHidden('type');
        $form->addText('title', 'Název')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'Vložit')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'insertCategoryFormSucceeded'];
        $form->onValidate[] = [$this, 'validateCategoryFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateCategoryFormSucceeded(BootstrapUIForm $form): void
    {
        $redirectTo = $this->presenter->getName();

        $category = $this->database->table('contacts_categories')->where([
            'parent_id' => $form->values->parent_id,
            'title' => $form->values->title,
        ]);

        if ($category->count() > 0) {
            $this->flashMessage('Kategorie již existuje', 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }

        if ($form->values->title === '') {
            $this->flashMessage('Kategorie musí mít název', 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertCategoryFormSucceeded(BootstrapUIForm $form): void
    {
        if (is_numeric($form->values->type)) {
            $slugName = new Slug($this->database);
            $slugId = $slugName->insert($form->values->title, $form->values->type);
        }

        $category = new Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent_id, $slugId);

        $redirectTo = $this->presenter->getName();

        $this->presenter->redirect(':' . $redirectTo . ':default', ['id' => $form->values->parent_id]);
    }

    /**
     * @param type $id
     * @param type $type Specifies type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render($id = null, $type = null)
    {
        $template = $this->getTemplate();
        $template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/InsertContactCategoryControl.latte');

        $template->id = $id;
        $template->idActive = $this->presenter->getParameter('id');

        if ($id === null) {
            $template->menu = $this->database->table('contacts_categories');
        } else {
            $template->menu = $this->database->table('contacts_categories')->where('parent_id', $id);
        }

        $template->render();
    }

}