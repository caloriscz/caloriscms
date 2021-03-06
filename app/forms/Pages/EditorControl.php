<?php

namespace App\Forms\Pages;

use App\Model\Document;
use HTMLPurifier;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

/**
 * Wysiwyg editor for editing pages and snippets
 * Class EditorControl
 * @package Caloriscz\Page\Editor
 */
class EditorControl extends Control
{
    private HTMLPurifier $htmlPurifier;
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedAttributes', 'img.src,*.style,*.class');
        $config->set('Attr.AllowedClasses', 'col-*,container,text-right, text-left, btn, btn-lg');
        $config->set('HTML.ForbiddenElements', ['font']);
        $config->set('AutoFormat.RemoveEmpty', true);

        $this->htmlPurifier = new \HTMLPurifier($config);
    }

    protected function createComponentLangSelector(): \LangSelectorControl
    {
        return new \LangSelectorControl($this->database);
    }

    /**
     * Edit page content
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $pages = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $l = $this->presenter->getParameter('l');
        $enabled = true;

        if ($this->presenter->template->member->users_roles->pages) {
            $enabled = false;
        }

        $form->addHidden('id');
        $form->addHidden('l');
        $form->addHidden('docs_id');
        $form->addTextArea('document')->setDisabled($enabled);

        if (null === $l || $l === '') {
            $form->setDefaults([
                'id' => $pages->id,
                'document' => $pages->document,
            ]);
        } else {
            $form->setDefaults([
                'id' => $pages->id,
                'l' => $l,
                'document' => $pages->{'document_' . $l},
            ]);
        }

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionFormValidated'];
        $form->addSubmit('submit', 'Uložit')
            ->setHtmlId('formxins');

        return $form;
    }

    /**
     * @throws AbortException
     */
    public function permissionFormValidated(): void
    {
        if ($this->getPresenter()->template->member->users_roles->pages === 0) {
            $this->getPresenter()->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->getPresenter()->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $document = $this->purify($form->values->document);

        $doc = new Document($this->database);
        $doc->setLanguage($form->values->l);
        $doc->setDocument($document);
        $doc->save($form->values->id, $this->presenter->user->getId());

        $this->getPresenter()->redirect('this', ['id' => $form->values->id, 'l' => $form->values->l]);
    }

    /**
     * Toggle display
     * @throws AbortException
     */
    public function handleToggle(): void
    {
        setcookie('editortype', $this->getParameter('editortype'), time() + 15552000);

        $this->getPresenter()->redirect('this', ['id' => $this->getParameter('id')]);
    }

    /**
     * @param $dirtyHtml
     * @return string
     */
    public function purify($dirtyHtml): string
    {
        return $this->htmlPurifier->purify($dirtyHtml);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->editortype = $this->getPresenter()->request->getCookie('editortype');
        $template->pages = $this->database->table('pages')->where('NOT id', $this->getPresenter()->getParameter('id'));
        $template->page = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));

        $template->templates = $this->database->table('pages_templates')->where('pages_types_id IS NULL')->order('title');
        $template->enabled = false;

        if ($this->getPresenter()->template->member->users_roles->pages) {
            $this->template->enabled = true;
        }

        $template->page_id = $this->getPresenter()->getParameter('id');
        $template->setFile(__DIR__ . '/EditorControl.latte');
        $template->render();
    }

}