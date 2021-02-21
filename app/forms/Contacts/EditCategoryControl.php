<?php
namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditCategoryControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $category = $this->database->table('pages')->get($this->presenter->getParameter('id'));
        $categories = $this->database->table('pages')->where('pages_types_id', 7)->fetchPairs('id', 'title');

        if ($category === null) {
        unset($categories[$category->id]);
        }

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addText('title', 'Název');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setAttribute('class', 'form-control');
        $form->addText('url', 'Odkaz');
        $form->addSubmit('submitm', 'Uložit');

        if ($category === null) {
            $arr = [
                'id' => $category->id,
                'title' => $category->title,
                'parent' => $category->pages_id,
            ];
        }

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
        $this->database->table('contacts_categories')->get($form->values->id)
            ->update([
                'title' => $form->values->title,
                'parent_id' => $form->values->parent,
            ]);

        $this->presenter->redirect('this', ['id' => $form->values->id]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditCategoryControl.latte');
        $template->render();
    }

}