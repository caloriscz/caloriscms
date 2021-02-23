<?php
namespace App\Forms\Snippets;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class EditFormControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit page content
     */
    public function createComponentEditSnippetForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();

        $snippet = $this->database->table('snippets')->get($this->getPresenter()->getParameter('id'));

        $form->addHidden('snippet_id');
        $form->addHidden('pages_id');
        $form->addHidden('l');
        $form->addTextArea('content')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('height', '250px')
            ->setHtmlId('wysiwyg-sm');

        if ($this->getPresenter()->getParameter('l') == null) {
            $arr['content'] = $snippet->content;
        } else {
            $arr['content'] = $snippet->{'content_' . $this->getPresenter()->getParameter('l')};
            $arr['l'] = $this->getPresenter()->getParameter('l');
        }

        $arr['snippet_id'] = $this->presenter->getParameter('id');

        $form->setDefaults($arr);

        $form->onSuccess[] = [$this, 'editSnippetFormSucceeded'];

        $form->addSubmit('submitm', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-success')
            ->setHtmlId('formxins');

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editSnippetFormSucceeded(BootstrapUIForm $form): void
    {
        $langSuffix = '';
        $content = $form->getHttpData($form::DATA_TEXT, 'content');

        if ($form->values->l !== '') {
            $langSuffix = '_' . $form->values->l;
        }

        $this->database->table('snippets')->get($form->values->snippet_id)->update([
            'content' . $langSuffix => $content,
        ]);


        $this->presenter->redirect('this', [
            'id' => $form->values->snippet_id,
            'snippet' => $form->values->snippet_id,
            'l' => $form->values->l
        ]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditFormControl.latte');
        $this->template->render();
    }

}