<?php

namespace App\AdminModule\Presenters;

use App\Model\IO;
use Caloriscz\Media\FileListControl;
use Caloriscz\Media\MediaForms\ImageEditFormControl;
use Caloriscz\Page\Editor\BlockControl;
use Caloriscz\Page\Pages\PageListControl;
use Caloriscz\Page\Related\FilterFormControl;
use Caloriscz\Page\Snippets\EditFormControl;
use Caloriscz\Page\Snippets\InsertFormControl;

/**
 * Pages presenter.
 */
class PagesPresenter extends BasePresenter
{
    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function startup()
    {
        parent::startup();

        $this->template->type = $this->getParameter("type");
    }

    protected function createComponentPageList()
    {
        $control = new PageListControl($this->database, $this->em);
        $control->onSave[] = function ($type) {
            $this->redirect(this, array("type" => $type));
        };

        return $control;
    }

    protected function createComponentBlock()
    {
        $control = new BlockControl($this->database);
        return $control;
    }

    protected function createComponentEditSnippetForm()
    {
        $control = new EditFormControl($this->database);
        return $control;
    }

    protected function createComponentInsertSnippetForm()
    {
        $control = new InsertFormControl($this->database);
        return $control;
    }

    protected function createComponentPageFilterRelated()
    {
        $control = new FilterFormControl($this->database);
        return $control;
    }

    protected function createComponentInsertPageForm()
    {
        $control = new \Caloriscz\Page\PageForms\InsertFormControl($this->database, $this->em);
        return $control;
    }

    protected function createComponentLangSelector()
    {
        $control = new \LangSelectorControl($this->database);
        return $control;
    }

    protected function createComponentImageEditForm()
    {
        $control = new ImageEditFormControl($this->database);
        return $control;
    }

    protected function handleChangeState($id, $public)
    {
        if ($public == 0) {
            $idState = 1;
        } else {
            $idState = 0;
        }

        $this->database->table('pages')->get($id)
            ->update(array(
                'public' => $idState,
            ));

        $this->redirect(':Admin:Pages:default', array('id' => null));
    }

    public function handleDeleteRelated($id)
    {
        $this->database->table('pages_related')->get($id)->delete();
        $this->redirect(':Admin:Pages:detailRelated', array('id' => $this->getParameter('item')));
    }

    protected function createComponentProductFileList()
    {
        $control = new \Caloriscz\Page\File\FileListControl($this->database);
        $control->onSave[] = function ($pages_id) {
            $this->redirect(this, array("id" => $pages_id));
        };
        return $control;
    }

    /**
     * Delete image
     */
    public function handleDeleteImage($id)
    {
        $this->database->table('media')->get($id)->delete();

        IO::remove(APP_DIR . '/media/' . $id . '/' . $this->getParameter('name'));
        IO::remove(APP_DIR . '/media/' . $id . '/tn/' . $this->getParameter('name'));

        $this->redirect(':Admin:Pages:detailImages', array('id' => $this->getParameter('name'),));
    }

    public function handleInsertRelated($id)
    {
        $this->database->table('pages_related')->insert(array(
            'pages_id' => $this->getParameter('item'),
            'related_pages_id' => $id,
        ));
        $this->redirect(':Admin:Pages:detailRelated', array('id' => $this->getParameter('item')));
    }

    /**
     * Delete snippet
     */
    public function handleDeleteSnippet($id)
    {
        $this->database->table('snippets')->get($id)->delete();
        $this->redirect(':Admin:Pages:snippets', array('id' => $this->getParameter('page')));
    }

    public function renderDetail()
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderDetailImages()
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderSettings()
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderImagesDetail()
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderDetailFiles()
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->files = $this->database->table('media')
            ->where(array('pages_id' => $this->getParameter('id'), 'file_type' => 0));
    }

    public function renderSnippets()
    {
        $this->template->catalogue = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->snippets = $this->database->table('snippets')
            ->where(array('pages_id' => $this->getParameter('id')));
    }

    public function renderSnippetsDetail()
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->snippet = $this->database->table('snippets')->get($this->getParameter('snippet'));
    }

    public function renderDetailRelated()
    {
        $src = $this->getParameter('src');

        $this->template->relatedSearch = $this->database->table('pages')
            ->where(array('title LIKE ?' => '%' . $src . '%'))->limit(20);
        $this->template->related = $this->database->table('pages_related')
            ->where(array('pages_id' => $this->getParameter('id')));
        $this->template->catalogue = $this->database->table('pages')->get($this->getParameter('id'));
    }

}
