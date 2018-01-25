<?php

namespace App\AdminModule\Presenters;

use App\Model\Entity\Snippets;
use Caloriscz\Page\Editor\BlockControl;
use Caloriscz\Page\Snippets\EditFormControl;
use Caloriscz\Page\Snippets\InsertFormControl;

/**
 * Snippet handling
 * @package App\AdminModule\Presenters
 */
class SnippetsPresenter extends BasePresenter
{
    protected function createComponentEditSnippetForm()
    {
        return new EditFormControl($this->database);
    }

    protected function createComponentInsertSnippetForm()
    {
        return new InsertFormControl($this->database, $this->em);
    }

    protected function createComponentBlock()
    {
        return new BlockControl($this->database);
    }

    protected function createComponentLangSelector()
    {
        return  new \LangSelectorControl($this->em);
    }

    /**
     * Delete snippet
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $this->database->table('snippets')->get($id)->delete();
        $this->redirect('this', array('id' => $this->getParameter('page')));
    }

    public function renderDefault()
    {
        $this->template->snippets = $this->em->getRepository(Snippets::class)->findAll([], ['order' => 'ASC']);
    }

    public function renderDetail()
    {
        $this->template->snippet = $this->em->getRepository(Snippets::class)->find($this->getParameter('id'));
    }
}
