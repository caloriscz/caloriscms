<?php
namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

/**
 * Rename a contact category or move it under another one.
 */
class EditCategoryControl extends Control
{
    /** contacts_categories.title is varchar(80); strict MariaDB refuses a longer one with a 500. */
    public const TITLE_LENGTH = 80;

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Ids of the category and all its subcategories: none of them can become its parent.
     * @return int[]
     */
    public function subtreeIds(int $id): array
    {
        $parents = $this->database->table('contacts_categories')->fetchPairs('id', 'parent_id');
        $ids = [$id];
        for ($i = 0; $i < count($ids); $i++) {
            foreach ($parents as $child => $parent) {
                if ((int) $parent === $ids[$i] && !in_array((int) $child, $ids, true)) {
                    $ids[] = (int) $child;
                }
            }
        }

        return $ids;
    }

    protected function createComponentEditForm(): BootstrapUIForm
    {
        $category = $this->database->table('contacts_categories')->get($this->presenter->getParameter('id'));
        if ($category === null) {
            $this->presenter->error('Record not found.', 404);
        }
        $parents = $this->database->table('contacts_categories')
            ->where('id NOT', $this->subtreeIds((int) $category->id))
            ->order('title')
            ->fetchPairs('id', 'title');

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addText('title', 'Název')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('parent', 'Nadřazená kategorie', $parents)
            ->setPrompt('— žádná (hlavní kategorie) —')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->setDefaults([
            'id' => $category->id,
            'title' => $category->title,
            'parent' => $category->parent_id !== null && isset($parents[$category->parent_id]) ? $category->parent_id : null,
        ]);

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getValues();
        $category = $this->database->table('contacts_categories')->get($values->id);
        if ($category === null) {
            $this->presenter->error('Record not found.', 404);
        }
        $parent = $values->parent === null || $values->parent === '' ? null : (int) $values->parent;
        $title = (string) $values->title;

        if (trim($title) === '') {
            $form->addError('Kategorie musí mít název.');
        } elseif (mb_strlen($title, 'UTF-8') > self::TITLE_LENGTH) {
            $form->addError(sprintf('Název kategorie může mít nejvýše %d znaků. Nic nebylo uloženo.', self::TITLE_LENGTH));
        } elseif ($parent !== null && in_array($parent, $this->subtreeIds((int) $category->id), true)) {
            // The select already offers only allowed parents; this guards a forged value.
            $form->addError('Kategorii nelze přesunout pod ni samotnou ani pod její podkategorii.');
        } elseif ($this->database->table('contacts_categories')->where(['parent_id' => $parent, 'title' => $title])
                ->where('id != ?', $category->id)->count('*') > 0) {
            $form->addError('Kategorie s tímto názvem už na tomto místě existuje.');
        }
        if ($form->hasErrors()) {
            return;
        }

        $category->update([
            'title' => $title,
            'parent_id' => $parent,
        ]);

        $this->presenter->flashMessage('Kategorie byla uložena.', 'success');
        $this->presenter->redirect('this', ['id' => $category->id]);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditCategoryControl.latte');
        $template->render();
    }

}
