<?php
namespace App\Forms\Contacts;

use App\Model\Category;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class InsertCategoryControl extends Control
{

    public $database;

    /**
     * InsertCategoryControl constructor.
     * @param Explorer $database
     */
    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Insert Contact category
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();

        $form->addHidden('parent');
        $form->addText('title');
        $form->addSubmit('submitm');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'validateFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateFormSucceeded(BootstrapUIForm $form): void
    {
        $category = $this->database->table('contacts_categories')->where([
            'parent_id' => $form->values->parent,
            'title' => $form->values->title,
        ]);

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
        $category = new Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent);

        $this->presenter->redirect('this', ['id' => null]);
    }

    /**
     * @param null $identifier
     */
    public function render($identifier = null): void
    {
        $this->template->categoryId = $identifier;
        $this->template->setFile(__DIR__ . '/InsertCategoryControl.latte');
        $this->template->render();
    }
}