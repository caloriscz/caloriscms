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
 * Snippet handling
 */
class SnippetsPresenter extends BasePresenter
{
    public function __construct(Context $database, EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;
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

    protected function createComponentBlock()
    {
        $control = new BlockControl($this->database);
        return $control;
    }

    protected function createComponentLangSelector()
    {
        $control = new \LangSelectorControl($this->em);
        return $control;
    }

    /**
     * Delete snippet
     */
    public function handleDelete($id)
    {
        $this->database->table('snippets')->get($id)->delete();
        $this->redirect('this', array('id' => $this->getParameter('page')));
    }

    public function renderDefault()
    {
        $this->template->snippets = $this->database->table('snippets')->order('keyword');
    }

    public function renderDetail()
    {
        $this->template->snippet = $this->database->table('snippets')->get($this->getParameter('id'));
    }
}
