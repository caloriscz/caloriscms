<?php

namespace App\Forms\Menu;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Diagnostics\Debugger;
use Nette\Forms\BootstrapUIForm;
use Tracy\Bar;

class InsertMenuMenusControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'dictionary.main.Title')
            ->setAttribute('class', 'form-control');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute('class', 'btn btn-primary');

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
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.categoryAlreadyExists'), 'error');
            $this->presenter->redirect('this');
        }

        if ($form->values->title === '') {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.categoryMustHaveSomeName'), 'error');
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
        $template->languages = $this->database->table('languages')->where([
            'default' => null,
            'used' => 1,
        ]);

        $template->setFile(__DIR__ . '/InsertMenuMenusControl.latte');
        $template->render();
    }
}
