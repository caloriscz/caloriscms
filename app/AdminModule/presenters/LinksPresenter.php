<?php

namespace App\AdminModule\Presenters;

use App\Model\IO;
use Caloriscz\Links\CategoryPanelControl;
use Nette\Application\AbortException;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Random;

/**
 * Link management with categories
 */
class LinksPresenter extends BasePresenter
{

    /**
     * @throws AbortException
     */
    protected function startup()
    {
        parent::startup();

        $this->template->link = $this->database->table('links')->get($this->getParameter('id'));
    }

    /**
     * @return CategoryPanelControl
     */
    protected function createComponentCategoryPanel(): CategoryPanelControl
    {
        return new CategoryPanelControl($this->database);
    }

    /**
     * Insert contact
     */
    public function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addSubmit('submitm', 'Vytvořit nový odkaz')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @throws AbortException
     */
    public function insertFormSucceeded(): void
    {
        $id = $this->database->table('links')
            ->insert([
                'links_categories_id' => null,
            ]);

        $this->redirect(':Admin:Links:detail', ['id' => $id]);
    }

    /**
     * Delete contact
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id): void
    {
        $this->database->table('links')->get($id)->delete();

        $this->redirect(':Admin:Links:default', ['id' => null]);
    }

    /**
     * Edit contact
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $categories = $this->database->table('links_categories')->order('title')->fetchPairs('id', 'title');

        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('id');
        $form->addText('title', 'Název')
            ->setAttribute('placeholder', 'Název');
        $form->addText('url', 'Odkaz')
            ->setAttribute('placeholder', 'Odkaz');
        $form->addSelect('category', 'Kategorie', $categories)
            ->setAttribute('class', 'form-control');
        $form->addTextArea('description', 'Popisek')
            ->setAttribute('class', 'form-control summernote');
        $form->addUpload('the_file', 'Vyberte obrázek (nepovinné)');
        $form->setDefaults([
            'title' => $this->template->link->title,
            'url' => $this->template->link->url,
            //'category' => $this->template->link->links_categories_id,
            'description' => $this->template->link->description,
            'id' => $this->getParameter('id'),
        ]);

        $form->addSubmit('submitm', 'Uložit')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('links')
            ->where([
                'id' => $form->values->id,
            ])
            ->update([
                'title' => $form->values->title,
                'url' => $form->values->url,
                'links_categories_id' => $form->values->category,
                'description' => $form->values->description,
            ]);

        if ($_FILES['the_file']['size'] > 0) {
            IO::directoryMake(APP_DIR . '/links-media');

            if (file_exists(APP_DIR . '/links-media/link-' . $form->values->id . '.jpg') && is_uploaded_file($_FILES['the_file']['tmp_name'])) {
                IO::remove(APP_DIR . '/links-media/link-' . $form->values->id . '.jpg');
            }

            IO::upload(APP_DIR . '/links-media', 'link-' . $form->values->id . '.jpg');
        }

        $this->redirect('this', ['id' => $form->values->id]);
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleDeleteImage($id): void
    {
        IO::remove(APP_DIR . '/links-media/link-' . $id . '.jpg');
        $this->redirect(':Admin:Links:detail', ['id' => $id, 'rnd' => Random::generate(4)]);
    }

    /**
     * Delete group
     * @param $id
     * @throws AbortException
     */
    public function handleDeleteCategory($id): void
    {
        $this->database->table('links')->where(['links_categories_id' => $id])->update(['links_categories_id' => 1]);
        $this->database->table('links_categories')->get($id)->delete();
        $this->redirect('this');
    }

    public function renderDefault(): void
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

    public function renderCategories(): void
    {
        $this->template->categories = $this->database->table('links_categories')->order('category');
    }

}
