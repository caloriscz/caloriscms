<?php

namespace App\Forms\Pages;

use App\Model\Document;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditorSettingsControl extends Control
{
    private $htmlPurifier;

    /** @var Context */
    public $database;

    /** @var EntityManager @inject */
    public $em;

    public function __construct(Context $database, EntityManager $em)
    {
        $this->database = $database;
        $this->em = $em;

        $config = \HTMLPurifier_Config::createDefault();
        $this->htmlPurifier = new \HTMLPurifier($config);
    }

    protected function createComponentLangSelector()
    {
        return new \LangSelectorControl($this->em);
    }

    /**
     * Edit page content
     */
    public function createComponentEditForm()
    {
        $pages = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));
        $pagesCategories = $this->database->table('pages_categories')->order('title');

        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
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

        if ($pages->pages_types_id === 2) {
            $form->addSelect('pages_categories_id', '', $pagesCategories->fetchPairs('id', 'title'));
            $form->setDefaults(['pages_categories_id' => $pages->pages_categories_id]);
        }

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        $form->onValidate[] = [$this, 'permissionFormValidated'];
        $form->addSubmit('submit', 'dictionary.main.Save')
            ->setHtmlId('formxins');

        return $form;
    }

    public function permissionFormValidated()
    {
        if ($this->getPresenter()->template->member->users_roles->pages_edit === 0) {
            $this->getPresenter()->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->getPresenter()->redirect('this');
        }
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        $doc = new Document($this->database);
        $doc->setLanguage($form->values->l);
        $doc->setDatePublished($form->values->date_published);
        $doc->setCategory($form->values->pages_categories_id);
        $doc->setTitle($form->values->title);
        $doc->setTemplate($values['template']);
        $doc->setSlug($form->values->slug_old, $form->values->slug);
        $doc->setMetaKey($form->values->metakeys);
        $doc->setMetaDescription($form->values->metadesc);
        $doc->setSitemap(1);
        $doc->setParent($values['parent']);
        $doc->save($form->values->id, $this->getPresenter()->user->getId());

        $this->getPresenter()->redirect('this', ['id' => $form->values->id, 'l' => $form->values->l]);
    }

    public function handlePublic()
    {
        $page = $this->database->table('pages')->get($this->getPresenter()->getParameter('id'));
        $show = 1;

        if ($page->public === 1) {
            $show = 0;
        }

        $this->database->table('pages')->get($this->getPresenter()->getParameter('id'))->update(['public' => $show]);

        $this->getPresenter()->redirect('this', ['id' => $this->getPresenter()->getParameter('id'), 'l' => $this->getPresenter()->getParameter('l')]);
    }

    public function render()
    {
        $this->getTemplate()->settings = $this->getPresenter()->template->settings;
        $this->template->editortype = $this->getPresenter()->request->getCookie('editortype');

        $this->template->pages = $this->database->table('pages')->where('NOT id', $this->presenter->getParameter('id'));
        $this->template->page = $this->database->table('pages')->get($this->presenter->getParameter('id'));

        $this->template->templates = $this->database->table('pages_templates')->where('pages_types_id IS NULL')->order('title');

        if ($this->getPresenter()->template->member->users_roles->pages_document) {
            $this->template->enabled = true;
        } else {
            $this->template->enabled = false;
        }

        $this->template->page_id = $this->getPresenter()->getParameter('id');
        $this->template->setFile(__DIR__ . '/EditorSettingsControl.latte');
        $this->template->render();
    }

}