<?php

namespace App\AdminModule\Presenters;

use App\Model\IO;
use Caloriscz\Links\CategoryPanelControl;
use Nette\Application\AbortException;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Utils\Random;
use Tracy\Debugger;

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

    public function actionDetail(): void
    {
        $this->template->link = $this->requireRecord('links');
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
            ->setHtmlAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * @throws AbortException
     */
    public function insertFormSucceeded(): void
    {
        $category = !empty($this->getParameter('id')) ? (int) $this->getParameter('id') : 1;

        $link = $this->database->table('links')->insert([
            'links_categories_id' => $category,
            'title' => '',
        ]);
        $this->redirect(':Admin:Links:detail', ['id' => $link->id]);
    }

    /**
     * Delete contact
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id): void
    {

        (new \App\Model\AssetWrites($this->database, APP_DIR))->deleteLink((int) $id);
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
        // As the snippet and page forms show theirs; a size error is the only feedback.
        $form->getRenderer()->wrappers['error'] = ['container' => null, 'item' => 'div class="alert alert-danger"'];

        $form->addHidden('id');
        $form->addText('title', 'Název')
            ->setHtmlAttribute('placeholder', 'Název');
        $form->addText('url', 'Odkaz')
            ->setHtmlAttribute('placeholder', 'Odkaz');
        $form->addSelect('category', 'Kategorie', $categories)
            ->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('description', 'Popisek')
            ->setHtmlAttribute('class', 'form-control summernote');
        $form->addUpload('the_file', 'Vyberte obrázek (nepovinné)')
            ->addRule(Form::MIME_TYPE, \App\Model\AssetStorage::INVALID_IMAGE, IO::IMAGE_MIME_TYPES);
        $form->setDefaults([
            'title' => $this->template->link->title,
            'url' => $this->template->link->url,
            'category' => $this->template->link->links_categories_id,
            'description' => $this->template->link->description,
            'id' => $this->getParameter('id'),
        ]);

        $form->addSubmit('submitm', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $description = \App\Security\HtmlContent::latin1Html(\App\Security\HtmlContent::purify($form->values->description));
        $error = \App\Security\HtmlContent::textSizeError($description);
        if ($error !== null) {
            $form->addError($error);
            return;
        }

        try {
            (new \App\Model\AssetWrites($this->database, APP_DIR))->link((int) $form->values->id, [
                'title' => $form->values->title, 'url' => $form->values->url,
                'links_categories_id' => $form->values->category,
                'description' => $description,
            ], $_FILES['the_file'] ?? []);
        } catch (\Nette\Application\BadRequestException $error) {
            $this->flashMessage($error->getMessage(), 'error');
        }
        $this->redirect('this', ['id' => $form->values->id]);

    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleDeleteImage($id): void
    {

        (new \App\Model\AssetWrites($this->database, APP_DIR))->deleteLink((int) $id, true);
        $this->redirect(':Admin:Links:detail', ['id' => $id, 'rnd' => Random::generate(4)]);

    }

    public function renderDefault(): void
    {
        $this->template->categoryId = $this->getParameter('id');
        // Titles by id, not through each link's reference: that needs a foreign key in the
        // database, and without one the page stopped rendering at the first link.
        $this->template->categoryTitles = $this->database->table('links_categories')->fetchPairs('id', 'title');

        if ($this->getParameter('id')) {
            $this->template->links = $this->database->table('links')
                ->where('links_categories_id', $this->getParameter('id'))
                ->order('title');
        } else {
            $this->template->links = $this->database->table('links')->order('title');
        }
    }

}
