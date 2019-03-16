<?php

namespace App\AdminModule\Presenters;

use App\Forms\Media\DropZoneControl as DropZoneMediaControl;
use App\Forms\Pages\EditorSettingsControl;
use App\Forms\Pictures\DropZoneControl as DropZonePicturesControl;
use App\Forms\Media\ImageEditFormControl;
use App\Forms\Pages\FilterFormControl;
use Apps\Forms\Pages\InsertFormControl;
use Caloriscz\Media\ImageBrowserControl;
use Caloriscz\Page\FileListControl;
use Caloriscz\Page\PageListControl;
use Nette\Application\AbortException;

/**
 * Pages presenter.
 */
class PagesPresenter extends BasePresenter
{
    /**
     * @throws AbortException
     */
    public function startup()
    {
        parent::startup();

        $this->template->type = $this->getParameter('type');
    }

    protected function createComponentPageList(): PageListControl
    {
        $view = 'simple';

        if ($this->request->getCookie('view')) {
            $view = $this->request->getCookie('view');
        }

        $control = new PageListControl($this->database);
        $control->setView($view);
        $control->onSave[] = function ($type) {
            $this->redirect('this', ['type' => $type]);
        };

        return $control;
    }

    /**
     * @return FilterFormControl
     */
    protected function createComponentPageFilterRelated(): FilterFormControl
    {
        return new FilterFormControl($this->database);
    }

    /**
     * @return InsertFormControl
     */
    protected function createComponentInsertPageForm(): InsertFormControl
    {
        return new InsertFormControl($this->database);
    }

    /**
     * @return \LangSelectorControl
     */
    protected function createComponentLangSelector(): \LangSelectorControl
    {
        return new \LangSelectorControl($this->database);
    }

    /**
     * @return ImageEditFormControl
     */
    protected function createComponentImageEditForm(): ImageEditFormControl
    {
        return new ImageEditFormControl($this->database);
    }

    /**
     * @return DropZoneMediaControl
     */
    protected function createComponentDropZoneMedia(): DropZoneMediaControl
    {
        return new DropZoneMediaControl($this->database);
    }

    /**
     * @return DropZonePicturesControl
     */
    protected function createComponentDropZonePictures(): DropZonePicturesControl
    {
        return new DropZonePicturesControl($this->database);
    }

    /**
     * @return ImageBrowserControl
     */
    public function createComponentImageBrowser(): ImageBrowserControl
    {
        return new ImageBrowserControl($this->database);
    }

    /**
     * @return EditorSettingsControl
     */
    public function createComponentEditorSettings(): EditorSettingsControl
    {
        $control = new EditorSettingsControl($this->database);
        $control->onSave[] = function (array $querystring, string $error = null) {

            if ($error) {
                $this->flashMessage($error, 'error');
            }

            $this->redirect('this', $querystring);
        };

        return $control;
    }

    /**
     * Changes public state
     * @param $identifier
     * @param $public
     * @throws AbortException
     */
    protected function handleChangeState($identifier, $public): void
    {
        $idState = 0;

        if ($public === 0) {
            $idState = 1;
        }

        $this->database->table('pages')->get($identifier)->update(['public' => $idState]);

        $this->redirect('this', ['id' => null]);
    }

    protected function createComponentProductFileList(): FileListControl
    {
        $control = new FileListControl($this->database);
        $control->onSave[] = function ($pages_id) {
            $this->redirect('this', ['id' => $pages_id]);
        };
        return $control;
    }

    /**
     * @throws AbortException
     */
    public function handleView(): void
    {
        $this->response->setCookie('view', $this->getParameter('view'), '180 days');

        $this->redirect(':Admin:Pages:default', ['type' => $this->getParameter('type'), 'view' => $this->getParameter('view')]);
    }

    public function renderDefault(): void
    {
        if ($this->request->getCookie('view') !== null) {
            $this->template->view = $this->request->getCookie('view');
        } else {
            $this->template->view = 'simple';
        }
    }

    public function renderDetail(): void
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderDetailImages(): void
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderSettings(): void
    {
        $this->template->pages = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderImagesDetail(): void
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function renderDetailFiles(): void
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
        $this->template->files = $this->database->table('media')
            ->where(['pages_id' => $this->getParameter('id'), 'file_type' => 0]);
    }
}
