<?php

namespace App\Forms\Pages;

use App\Model\Document;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditorSettingsControl extends Control
{
    private $htmlPurifier;

    /** @var Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $this->htmlPurifier = new \HTMLPurifier($config);
    }

    protected function createComponentLangSelector(): \LangSelectorControl
    {
        return new \LangSelectorControl($this->database);
    }

    /**
     * Edit page content
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $pages = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));

        $form = new BootstrapUIForm();
        $l = $this->getPresenter()->getParameter('l');

        $form->addHidden('id');
        $form->addHidden('l');
        $form->addHidden('docs_id');

        if ($l === '') {
            $form->setDefaults([
                'id' => $pages->id,
            ]);
        } else {
            $form->setDefaults([
                'id' => $pages->id,
                'l' => $l,
            ]);
        }

        $form->addHidden('slug_old');
        $form->addGroup('');
        $form->addCheckbox('public');
        $form->addText('date_published');
        $form->addText('title');
        $form->addText('slug');
        $form->addSelect('parent');
        $form->addTextArea('metadesc');
        $form->addTextArea('metakeys');
        $form->addCheckbox('sitemap');

        if ($l == '') {
            $form->setDefaults([
                'id' => $pages->id,
                'slug' => $pages->slug,
                'slug_old' => $pages->slug,
                'metadesc' => $pages->metadesc,
                'metakeys' => $pages->metakeys,
                'title' => $pages->title,
                'public' => $pages->public,
                'date_published' => $pages->date_published,
                'sitemap' => $pages->sitemap,
            ]);
        } else {
            $form->setDefaults([
                'id' => $pages->id,
                'l' => $l,
                'slug' => $pages->{'slug_' . $l},
                'slug_old' => $pages->{'slug_' . $l},
                'metadesc' => $pages->{'metadesc_' . $l},
                'metakeys' => $pages->{'metakeys_' . $l},
                'title' => $pages->{'title_' . $l},
                'public' => $pages->public,
                'date_published' => $pages->date_published,
                'sitemap' => $pages->sitemap,
            ]);
        }

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionFormValidated'];
        $form->addSubmit('submit', 'Uložit')
            ->setHtmlId('formxins');

        return $form;
    }

    public function permissionFormValidated(BootstrapUIForm $form): void
    {
        if ($this->getPresenter()->template->member->users_roles->pages === 0) {
            $this->onSave(['id' => $form->values->id, 'l' => $form->values->l], 'Nemáte oprávnění k této akci');
        }
    }

    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        $doc = new Document($this->database);
        $doc->setForm($form->getValues());
        $doc->setLanguage($form->values->l);
        $doc->setTemplate($values['template']);
        $doc->setParent($values['parent']);
        $doc->setSlug($form->values->slug_old, $form->values->slug);
        $doc->save($form->values->id, $this->getPresenter()->user->getId());

        $this->onSave(['id' => $form->values->id, 'l' => $form->values->l]);
    }

    public function handlePublic(): void
    {
        $page = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));
        $show = 1;

        if ($page->public === 1) {
            $show = 0;
        }

        $this->database->table('pages')->get($this->getPresenter()->getParameter('id'))->update(['public' => $show]);
        $this->onSave(['id' => $this->getPresenter()->getParameter('id'), 'l' => $this->getPresenter()->getParameter('l')]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->getPresenter()->template->settings;
        $template->editortype = $this->getPresenter()->request->getCookie('editortype');

        $template->pages = $this->database->table('pages')->where('NOT id', $this->presenter->getParameter('id'));
        $template->page = $this->database->table('pages')->get($this->presenter->getParameter('id'));

        $template->templates = $this->database->table('pages_templates')->where('pages_types_id IS NULL')->order('title');
        $template->enabled = false;

        if ($this->getPresenter()->template->member->users_roles->pages) {
            $template->enabled = true;
        }

        $template->page_id = $this->getPresenter()->getParameter('id');
        $template->setFile(__DIR__ . '/EditorSettingsControl.latte');
        $template->render();
    }

}