<?php

namespace App\AdminModule\Presenters;

use App\Forms\Snippets\EditFormControl;
use App\Forms\Snippets\InsertFormControl;
use Caloriscz\Page\BlockControl;

/**
 * Snippet handling
 * @package App\AdminModule\Presenters
 */
class SnippetsPresenter extends BasePresenter
{
    protected function createComponentEditSnippetForm(): EditFormControl
    {
        return new EditFormControl($this->database);
    }

    protected function createComponentInsertSnippetForm(): InsertFormControl
    {
        return new InsertFormControl($this->database);
    }

    protected function createComponentBlock(): BlockControl
    {
        return new BlockControl($this->database);
    }

    protected function createComponentLangSelector(): \LangSelectorControl
    {
        return  new \LangSelectorControl($this->database);
    }

    /**
     * Delete snippet
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id): void
    {
        $this->database->table('snippets')->get($id)->delete();
        $this->redirect('this', array('id' => $this->getParameter('page')));
    }

    public function renderDefault(): void
    {
        $this->template->snippets = $this->database->table('snippets');
    }

    public function renderDetail(): void
    {
        $this->template->snippet = $this->database->table('snippets')->get($this->getParameter('id'));
    }
}
