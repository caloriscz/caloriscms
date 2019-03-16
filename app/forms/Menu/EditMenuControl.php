<?php
namespace App\Forms\Menu;

use App\Model\Menu;
use App\Model\Page;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditMenuControl extends Control
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
        $pages = new Page($this->database);
        $categoryAll = new Menu($this->database);
        $category = $this->database->table('menu')->get($this->presenter->getParameter('id'));
        $categories = $categoryAll->getAll();
        unset($categories[$category->id]);

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popisek')
            ->setAttribute('class', 'form-control');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setAttribute('class', 'form-control');
        $form->addSelect('page', 'Vyberte stránku', $pages->getPageList())
            ->setPrompt('Stránka vybrána manuálně')
            ->setAttribute('class', 'form-control');
        $form->addText('url', 'Odkaz');
        $form->addSubmit('submitm', 'Uložit');

        $arr = [
            'id' => $category->id,
            'title' => $category->title,
            'description' => $category->description,
            'page' => $category->pages_id,
            'parent' => $category->parent_id,
            'url' => $category->url,
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
        $this->database->table('menu')->get($form->values->id)
            ->update([
                'title' => $form->values->title,
                'description' => $form->values->description,
                'pages_id' => $form->values->page,
                'parent_id' => $form->values->parent,
                'url' => $form->values->url,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->id]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->languages = $this->database->table('languages')->where('default', null);
        $template->setFile(__DIR__ . '/EditMenuControl.latte');
        $template->render();
    }

}
