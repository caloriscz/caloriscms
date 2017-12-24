<?php
namespace Caloriscz\Page\Snippets;

use Nette\Application\UI\Control;
use Nette\Forms\BootstrapUIForm;

class EditFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit page content
     */
    public function createComponentEditSnippetForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);

        $snippet = $this->database->table('snippets')->get($this->getPresenter()->getParameter('id'));

        $form->addHidden('page_id');
        $form->addHidden('snippet_id');
        $form->addHidden('pages_id');
        $form->addHidden('l');
        $form->addTextArea('content')
            ->setAttribute('class', 'form-control')
            ->setAttribute('height', '250px')
            ->setHtmlId('wysiwyg-sm');

        if ($this->getPresenter()->getParameter('l') === null) {
            $arr['content'] = $snippet->content;
        } else {
            $arr['content'] = $snippet->{'content_' . $this->getPresenter()->getParameter('l')};
            $arr['l'] = $this->getPresenter()->getParameter('l');
        }

        $arr['page_id'] = $this->getPresenter()->getParameter('id');
        $arr['snippet_id'] = $this->getPresenter()->getParameter('snippet');

        $form->setDefaults($arr);

        $form->onSuccess[] = [$this, 'editSnippetFormSucceeded'];

        $form->addSubmit('submitm', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-success')
            ->setHtmlId('formxins');

        return $form;
    }

    public function editSnippetFormSucceeded(BootstrapUIForm $form)
    {
        $langSuffix = '';
        $content = $form->getHttpData($form::DATA_TEXT, 'content');

        if ($form->values->l !== '') {
            $langSuffix = '_' . $form->values->l;
        }

        $this->database->table('snippets')->get($form->values->snippet_id)->update([
            'content' . $langSuffix => $content,
        ]);

        $this->getPresenter()->redirect('this', [
            'id' => $form->values->page_id,
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