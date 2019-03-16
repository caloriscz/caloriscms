<?php

namespace App\Forms\Menu;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class InsertMenuControl extends Control
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
        $form->getElementPrototype()->class = 'form-horizontal';

        $languages = $this->database->table('languages')->where(['default' => null, 'used' => 1]);

        if ($languages->count() > 1) {
            $form->addGroup('čeština');
        }

        $form->addHidden('parent');
        $form->addHidden('menu_menus_id');
        $form->addText('title', 'Název')
            ->setAttribute('class', 'form-control');
        $form->addText('url', 'Odkaz')
            ->setAttribute('class', 'form-control');

        foreach ($languages as $item) {
            $form->addGroup($item->title);

            $form->addText('title_' . $item->code, 'Název')
                ->setAttribute('class', 'form-control');
            $form->addText('url_' . $item->code, 'Odkaz')
                ->setAttribute('class', 'form-control');
        }

        $form->addSubmit('submitm', 'Vložit')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onSuccess[] = [$this, 'validateFormSucceeded'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateFormSucceeded(BootstrapUIForm $form): void
    {
        $arr = [
            'parent_id' => $form->values->parent,
            'menu_menus_id' => $form->values->menu_menus_id,
            'url' => $form->values->url,
            'title' => $form->values->title,
        ];

        $category = $this->database->table('menu')->where($arr);

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
        if (is_numeric($form->values->parent) === false) {
            $arr['parent_id'] = null;
        } else {
            $arr['parent_id'] = $form->values->parent;
        }

        $arr['title'] = $form->values->title;
        $arr['url'] = $form->values->url;

        $languages = $this->database->table('languages')->where([
            'default' => null,
            'used' => 1,
        ]);

        foreach ($languages as $item) {
            $arr['url_' . $item->code] = $form->values->{'url_' . $item->code};
            $arr['title_' . $item->code] = $form->values->{'title_' . $item->code};
        }

        $this->database->table('menu')->insert($arr);
        $this->database->query('SET @i = 1;UPDATE `menu` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');

        $this->presenter->redirect('this', ['id' => null]);
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

        $template->setFile(__DIR__ . '/InsertMenuControl.latte');
        $template->render();
    }
}
