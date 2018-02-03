<?php
namespace App\Forms\Contacts;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditCategoryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentEditForm()
    {
        $category = $this->database->table('pages')->get($this->presenter->getParameter('id'));
        $categories = $this->database->table('pages')->where('pages_types_id', 7)->fetchPairs('id', 'title');
        unset($categories[$category->id]);

        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('admin.categories.NothingRelated')
            ->setAttribute('class', 'form-control');
        $form->addText('url', 'dictionary.main.URL');
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $arr = array(
            'id' => $category->id,
            'title' => $category->title,
            'parent' => $category->pages_id,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('contacts_categories')->get($form->values->id)
            ->update([
                'title' => $form->values->title,
                'parent_id' => $form->values->parent,
            ]);

        $this->presenter->redirect('this', array('id' => $form->values->id));
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/EditCategoryControl.latte');
        $template->render();
    }

}