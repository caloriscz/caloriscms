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
use Kdyby\Doctrine\EntityManager;
use Nette\Database\Context;

/**
 * Pages presenter.
 */
class PagesPresenter extends BasePresenter
{
    public function __construct(Context $database, EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;
    }

    public function startup()
    {
        parent::startup();

        $this->template->type = $this->getParameter('type');
    }

    protected function createComponentPageList()
    {
        $control = new PageListControl($this->database, $this->em);
        $control->onSave[] = function ($type) {
            $this->redirect(this, array('type' => $type));
        };

        return $control;
    }

    protected function createComponentBlock()
    {
        return new BlockControl($this->database);
    }

    protected function createComponentPageFilterRelated()
    {
        return new FilterFormControl($this->database);
    }

    protected function createComponentInsertPageForm()
    {
        return new \Caloriscz\Page\PageForms\InsertFormControl($this->database, $this->em);
    }

    protected function createComponentLangSelector()
    {
        return new \LangSelectorControl($this->em);
    }

    protected function createComponentImageEditForm()
    {
        return new ImageEditFormControl($this->database);
    }

    protected function handleChangeState($id, $public)
    {
        if ($public === 0) {
            $idState = 1;
        } else {
            $idState = 0;
        }

        $this->database->table('pages')->get($id)->update(['public' => $idState]);

        $this->redirect('this', ['id' => null]);
    }

    public function handleDeleteRelated($id)
    {
        $this->database->table('pages_related')->get($id)->delete();
        $this->redirect(':Admin:Pages:detailRelated', ['id' => $this->getParameter('item')]);
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

        $this->redirect(':Admin:Pages:detailImages', ['id' => $this->getParameter('name')]);
    }

    public function handleInsertRelated($id)
    {
        $this->database->table('pages_related')->insert(array(
            'pages_id' => $this->getParameter('item'),
            'related_pages_id' => $id,
        ));
        $this->redirect(':Admin:Pages:detailRelated', ['id' => $this->getParameter('item')]);
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
            ->where(['pages_id' => $this->getParameter('id'), 'file_type' => 0]);
    }

    public function renderDetailRelated()
    {
        $src = $this->getParameter('src');

        $this->template->relatedSearch = $this->database->table('pages')
            ->where(['title LIKE ?' => '%' . $src . '%'])->limit(20);
        $this->template->related = $this->database->table('pages_related')
            ->where(['pages_id' => $this->getParameter('id')]);
        $this->template->catalogue = $this->database->table('pages')->get($this->getParameter('id'));
    }

}
