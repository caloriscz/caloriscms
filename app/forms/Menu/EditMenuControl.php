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
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('admin.categories.NothingRelated')
            ->setAttribute('class', 'form-control');
        $form->addSelect('page', 'admin.categories.SelectPage', $pages->getPageList())
            ->setPrompt('admin.categories.PageSelectedManually')
            ->setAttribute('class', 'form-control');
        $form->addText('url', 'dictionary.main.URL');
        $form->addSubmit('submitm', 'dictionary.main.Save');

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
