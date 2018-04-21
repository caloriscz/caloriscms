<?php

namespace App\AdminModule\Presenters;

use App\Model\IO;
use Caloriscz\Links\LinkForms\CategoryPanelControl;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Random;
use Nette\Utils\Strings;

/**
 * Link management with categories
 */
class LinksPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->link = $this->database->table('links')
            ->get($this->getParameter('id'));
    }

    protected function createComponentCategoryPanel()
    {

        return new CategoryPanelControl($this->database);
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addSubmit('submitm', 'Vytvořit nový odkaz')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    function insertFormSucceeded()
    {
        $id = $this->database->table('links')
            ->insert([
                'links_categories_id' => null,
            ]);

        $this->redirect(':Admin:Links:detail', ['id' => $id]);
    }

    /**
     * Delete contact
     */
    function handleDelete($id)
    {
        $this->database->table('links')->get($id)->delete();

        $this->redirect(':Admin:Links:default', ['id' => null]);
    }

    /**
     * Edit contact
     */
    function createComponentEditForm()
    {
        $categories = $this->database->table('links_categories')->order('title')->fetchPairs('id', 'title');

        $form = new BootstrapUIForm();
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title')
            ->setAttribute('placeholder', 'dictionary.main.Title');
        $form->addText('url', 'dictionary.main.URL')
            ->setAttribute('placeholder', 'dictionary.main.URL');
        $form->addSelect('category', 'dictionary.main.Category', $categories)
            ->setAttribute('class', 'form-control');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control')
            ->setHtmlId('wysiwyg');
        $form->addUpload('the_file', 'Vyberte obrázek (nepovinné)');
        $form->setDefaults([
            'title' => $this->template->link->title,
            'url' => $this->template->link->url,
            'category' => $this->template->link->links_categories_id,
            'description' => $this->template->link->description,
            'id' => $this->getParameter('id'),
        ]);

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('links')
            ->where(array(
                'id' => $form->values->id,
            ))
            ->update(array(
                'title' => $form->values->title,
                'url' => $form->values->url,
                'links_categories_id' => $form->values->category,
                'description' => $form->values->description,
            ));

        $uid = Strings::padLeft($form->values->id, 6, '0');

        if (file_exists(APP_DIR . '/links/' . $uid . '.jpg') && is_uploaded_file($_FILES['the_file']['tmp_name'])) {
            IO::remove(APP_DIR . '/links/' . $uid . '.jpg');
        }
        
        IO::upload(APP_DIR . '/links', $uid . '.jpg', 0644);

        $this->redirect(':Admin:Links:detail', ['id' => $form->values->id]);
    }

    function handleDeleteImage($id)
    {
        IO::remove(APP_DIR . '/links/' . Strings::padLeft($id, 6, '0') . '.jpg');
        $this->redirect(':Admin:Links:detail', ['id' => $id, 'rnd' => Random::generate(4)]);
    }

    /**
     * Delete group
     */
    function handleDeleteCategory($id)
    {
        if ($id === 1) {
            $this->flashMessage($this->translator->translate('messages.sign.CantDeleteMainGroup'), 'error');
            $this->redirect(':Admin:Links:categories');
        }

        $this->database->table('links')->where(['links_categories_id' => $id])->update(['links_categories_id' => 1]);

        $this->database->table('links_categories')->get($id)->delete();

        $this->redirect(':Admin:Links:categories');
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->getParameter('id');

        if ($this->getParameter('id')) {
            $this->template->links = $this->database->table('links')
                ->where('links_categories_id', $this->getParameter('id'))
                ->order('title');
        } else {
            $this->template->links = $this->database->table('links')->order('title');
        }
    }

    public function renderCategories()
    {
        $this->template->categories = $this->database->table('links_categories')->order('category');
    }

}
