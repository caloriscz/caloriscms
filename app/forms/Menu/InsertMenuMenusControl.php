<?php

namespace App\Forms\Menu;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class InsertMenuMenusControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('title', 'Název')
            ->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'Vložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onValidate[] = [$this, 'validateFormSucceeded'];
        $form->onSuccess[] = [$this, 'insertFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateFormSucceeded(BootstrapUIForm $form): void
    {
        $arr = [
            'title' => $form->values->title,
        ];

        $category = $this->database->table('menu_menus')->where($arr);

        if ($category->count() > 0) {
            $this->presenter->flashMessage('Kategorie již existuje', 'error');
            $this->presenter->redirect('this');
        }

        if ($form->values->title === '') {
            $this->presenter->flashMessage('Kategorie musí mít název', 'error');
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $arr['title'] = $form->values->title;
        $arr['description'] = $form->values->description;

        $menuMenus = $this->database->table('menu_menus')->insert($arr);

        $arrMenu = [
            'menu_menus_id' => $menuMenus->id,
            'title' => $arr['title']
        ];

        // Add main menu to tree
        $this->database->table('menu')->insert($arrMenu);


        $this->presenter->redirect('this', ['id' => $menuMenus->id]);
    }

    /**
     * @param null $menuId
     */
    public function render($menuId = null): void
    {
        $template = $this->getTemplate();
        $template->menuId = $menuId;
        $template->languages = $this->database->table('languages')->where(['default' => null, 'used' => 1]);

        $template->setFile(__DIR__ . '/InsertMenuMenusControl.latte');
        $template->render();
    }
}
