<?php

namespace App\AdminModule\Presenters;

use App\Forms\Media\DropZoneControl as DropZoneMediaControl;
use App\Forms\Pictures\DropZoneControl as DropZonePicturesControl;
use App\Forms\Pages\EditorSettingsControl;
use App\Forms\Media\ImageEditFormControl;
use App\Forms\Pages\FilterFormControl;
use Apps\Forms\Pages\InsertFormControl;
use Caloriscz\Media\ImageBrowserControl;
use Caloriscz\Page\FileListControl;
use Caloriscz\Page\PageListControl;
use Nette\Application\AbortException;
use Tracy\Debugger;

/**
 * Pages presenter for administering page content and settings.
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
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
    }

    protected function createComponentPageList(): PageListControl
    {
        $control = new PageListControl($this->database);
        $control->setView($this->request->getCookie('view'));
        $control->onSave[] = function ($type) {
            $this->redirect('this', ['type' => $type]);
        };

        return $control;
    }

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
        // Only a view that exists is stored; an old "Strom" link (view=tree) stores the list.
        $view = PageListControl::normalizeView($this->getParameter('view'));
        $this->response->setCookie('view', $view, '180 days');

        $this->redirect(':Admin:Pages:default', ['type' => $this->getParameter('type'), 'view' => $view]);
    }

    public function renderDefault(): void
    {
        $this->template->view = PageListControl::normalizeView($this->request->getCookie('view'));
    }

    public function actionPreview(int $id): void
    {
        $page = $this->database->table('pages')->get($id);

        if (!$page) {
            $this->error('Page not found');
        }

        $destination = $this->getPreviewDestination($page);

        if (!$destination) {
            $this->error('Preview destination not found');
        }

        $params = ['page_id' => $page->id];
        $locale = $this->getParameter('locale');

        if ($locale) {
            $params['locale'] = $locale;
        }

        $this->forward($destination, $params);
    }

    public function actionDetail(): void
    {
        $this->template->pages = $this->requireRecord('pages');
    }

    public function actionDetailImages(): void
    {
        $this->template->pages = $this->requireRecord('pages');
    }

    public function actionSettings(): void
    {
        $this->template->pages = $this->requireRecord('pages');
    }

    public function renderImagesDetail(): void
    {
        $this->template->page = $this->database->table('pages')->get($this->getParameter('id'));
    }

    public function actionDetailFiles(): void
    {
        $this->template->page = $this->requireRecord('pages');
        $this->template->files = $this->database->table('media')
            ->where(['pages_id' => $this->template->page->id, 'file_type' => 0]);
    }

    private function getPreviewDestination($page): ?string
    {
        if ($page->pages_templates_id !== null) {
            return $this->getTemplateDestination($page->ref('pages_templates', 'pages_templates_id'));
        }

        $pageType = $page->ref('pages_types', 'pages_types_id');

        if (!$pageType) {
            return null;
        }

        if ($pageType->pages_templates_id !== null) {
            $pageTemplate = $this->database->table('pages_templates')->get($pageType->pages_templates_id);
            $destination = $this->getTemplateDestination($pageTemplate);

            if ($destination) {
                return $destination;
            }
        }

        if ($page->pages_types_id === 9 && $page->presenter) {
            return $this->getPresenterDestination($page->presenter);
        }

        return $this->getPresenterDestination($pageType->presenter . ':' . $pageType->action);
    }

    private function getTemplateDestination($pageTemplate): ?string
    {
        if (!$pageTemplate) {
            return null;
        }

        return $this->getPresenterDestination($pageTemplate->template);
    }

    private function getPresenterDestination(string $destination): ?string
    {
        $templateInfo = explode(':', $destination);

        if (count($templateInfo) !== 3) {
            return null;
        }

        return ':' . implode(':', $templateInfo);
    }
}
