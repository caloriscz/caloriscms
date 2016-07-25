<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;

class SettingsControl extends Control
{
    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Page settings
     */
    function createComponentSetForm()
    {
        $pageId = $this->presenter->getParameter("id");
        $pages = $this->database->table("pages")->get($pageId);
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $l = $this->presenter->getParameter("l");

        $pageArr = $this->database->table("pages")->where(array(
            "pages_types_id" => array(0, 1),
            "NOT id" => $pageId
        ));

        $form->addHidden("id");
        $form->addHidden("l");
        $form->addHidden("slug_old");
        $form->addGroup("");
        $form->addCheckbox("public", "dictionary.main.PublishedForm");
        $form->addText("date_published", "Datum zveřejnění")
            ->setAttribute("class", "datetimepicker");
        $form->addText("title", "dictionary.main.Title");
        $form->addText("slug", "dictionary.main.Slug");
        $form->addGroup("dictionary.main.MetaTags");
        $form->addTextArea("metadesc", "dictionary.main.MetaDesc")
            ->setAttribute("class", "form-control");
        $form->addTextArea("metakeys", "dictionary.main.MetaKeys")
            ->setAttribute("class", "form-control");
        $form->addSelect("parent", "Nadřazená stránka", $pageArr->fetchPairs('id', 'title'))
            ->setPrompt("nepřiřazeno")
            ->setAttribute("class", "form-control");

        if ($l == '') {
            $form->setDefaults(array(
                "id" => $pages->id,
                "slug" => $pages->slug,
                "slug_old" => $pages->slug,
                "metadesc" => $pages->metadesc,
                "metakeys" => $pages->metakeys,
                "title" => $pages->title,
                "public" => $pages->public,
                "date_published" => $pages->date_published,
                "parent" => $pages->pages_id
            ));
        } else {
            $form->setDefaults(array(
                "id" => $pages->id,
                "l" => $l,
                "slug" => $pages->{'slug_' . $l},
                "slug_old" => $pages->{'slug_' . $l},
                "metadesc" => $pages->{'metadesc_' . $l},
                "metakeys" => $pages->{'metakeys_' . $l},
                "title" => $pages->{'title_' . $l},
                "public" => $pages->public,
                "date_published" => $pages->date_published,
                "parent" => $pages->pages_id
            ));
        }

        $form->onSuccess[] = $this->setFormSucceeded;
        $form->addSubmit("submit", "dictionary.main.Save")
            ->setHtmlId('formxins');

        return $form;
    }

    function setFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setLanguage($form->values->l);
        $doc->setPublic($form->values->public);
        $doc->setDatePublished($form->values->date_published);
        $doc->setTitle($form->values->title);
        $doc->setSlug($form->values->slug_old, $form->values->slug);
        $doc->setMetaKey($form->values->metakeys);
        $doc->setMetaDescription($form->values->metadesc);
        $doc->setParent($form->values->parent);
        $doc->save($form->values->id, $this->presenter->user->getId());

        $this->presenter->redirect(this, array("id" => $form->values->id, "l" => $form->values->l));
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/SettingsControl.latte');

        $template->render();
    }

}