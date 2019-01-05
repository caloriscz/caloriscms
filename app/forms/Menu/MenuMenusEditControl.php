<?php
namespace App\Forms\Menu;

use App\Model\Menu;
use App\Model\Page;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class MenuMenusEditControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit Menu item form
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $menuMenus = $this->database->table("menu_menus")->get($this->presenter->getParameter("id"));

        $form = new BootstrapUIForm();
        $form->addHidden('id');
        $form->addText('title');
        $form->addText('class');
        $form->addTextArea('description');
        $form->addSelect('type', 'Typ menu', ['Menu' => 'Menu', 'BadgesMenu' => 'BadgesMenu'])
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm');

        $arr = [
            'id' => $menuMenus->id,
            'title' => $menuMenus->title,
            'class' => $menuMenus->class,
            'description' => $menuMenus->description,
            'type' => $menuMenus->type
        ];

        $form->setDefaults(array_filter($arr));
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('menu_menus')->get($form->values->id)
            ->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
                'type' => $form->values->type,
                'class' => $form->values->class,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->languages = $this->database->table('languages')->where('default', null);
        $template->setFile(__DIR__ . '/MenuMenusEditControl.latte');
        $template->render();
    }

}
