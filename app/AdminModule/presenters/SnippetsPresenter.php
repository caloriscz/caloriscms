<?php

namespace App\AdminModule\Presenters;

use App\Forms\Snippets\EditFormControl;
use App\Forms\Snippets\InsertFormControl;
use App\Model\Entity\Snippets;
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
        return new InsertFormControl($this->database, $this->em);
    }

    protected function createComponentBlock(): BlockControl
    {
        return new BlockControl($this->database);
    }

    protected function createComponentLangSelector(): \LangSelectorControl
    {
        return  new \LangSelectorControl($this->em);
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
        $this->template->snippets = $this->em->getRepository(Snippets::class)->findAll([], ['order' => 'ASC']);
    }

    public function renderDetail(): void
    {
        $this->template->snippet = $this->em->getRepository(Snippets::class)->find($this->getParameter('id'));
    }
}
