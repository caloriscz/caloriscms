<?php

namespace Caloriscz\Page\Editor;

use Nette\Application\UI\Control;

class EditorControl extends Control
{

    private $htmlPurifier;

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $this->htmlPurifier = new \HtmlPurifier($config);
    }

    /**
     * Edit page content
     */
    function createComponentEditForm()
    {
        $pages = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $l = $this->presenter->getParameter("l");

        if ($this->presenter->template->member->users_roles->pages_document) {
            $enabled = false;
        } else {
            $enabled = true;
        }

        $form->addHidden("id");
        $form->addHidden("l");
        $form->addHidden("docs_id");
        $form->addTextArea("document")
            ->setAttribute("class", "form-control")
            ->setDisabled($enabled);

        if ($l == '') {
            $form->setDefaults(array(
                "id" => $pages->id,
                "document" => $pages->document,
            ));
        } else {
            $form->setDefaults(array(
                "id" => $pages->id,
                "l" => $l,
                "document" => $pages->{'document_' . $l},
            ));
        }

        $form->addHidden("slug_old");
        $form->addGroup("");
        $form->addCheckbox("public");
        $form->addText("date_published");
        $form->addText("title");
        $form->addText("slug");
        $form->addText("document2");
        $form->addSelect("parent");
        $form->addTextArea("metadesc");
        $form->addTextArea("metakeys");
        $form->addCheckbox("sitemap");

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
                "sitemap" => $pages->sitemap,
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
                "sitemap" => $pages->sitemap,
            ));
        }

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->onValidate[] = $this->permissionValidated;
        $form->addSubmit("submit", "dictionary.main.Save")
            ->setHtmlId('formxins');

        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        if ($this->presenter->template->member->users_roles->pages_edit == 0) {
            $this->presenter->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        $doc = new \App\Model\Document($this->database);
        $doc->setLanguage($form->values->l);

        //$document = $this->purify($form->values->document);

        $doc->setDocument($form->values->document);
        $doc->setLanguage($form->values->l);
        $doc->setDatePublished($form->values->date_published);
        $doc->setTitle($form->values->title);
        $doc->setTemplate($values["template"]);
        $doc->setSlug($form->values->slug_old, $form->values->slug);
        $doc->setMetaKey($form->values->metakeys);
        $doc->setMetaDescription($form->values->metadesc);
        $doc->setSitemap(1);
        $doc->setParent($values["parent"]);
        $doc->save($form->values->id, $this->presenter->user->getId());

        $this->presenter->redirect(this, array("id" => $form->values->id, "l" => $form->values->l));
    }

    /**************************************
     * Image add editor
     */
    function createComponentImageAddForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->onSuccess[] = $this->imageAddFormSucceeded;
        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        return $form;
    }

    function imageAddFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input
        $fileName = $_FILES['file']['name'];

        if ($_FILES['file']['name']) {
            if (!$_FILES['file']['error']) {
                $destination = APP_DIR . '/media/' . $values["page_id"] . '/' . $fileName; //change this directory
                $location = $_FILES["file"]["tmp_name"];

                move_uploaded_file($location, $destination);
                chmod($destination, 0644);

                $fileSize = filesize($destination);

                $checkImage = $this->database->table("media")->where(array(
                    'name' => $fileName,
                    'pages_id' => $values["page_id"],
                ));

                if ($checkImage->count() == 0) {
                    $image = \Nette\Utils\Image::fromFile($destination);
                    $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
                    $image->sharpen();
                    $image->save(APP_DIR . '/media/' . $values["page_id"] . '/tn/' . $fileName);
                    chmod(APP_DIR . '/media/' . $values["page_id"] . '/tn/' . $fileName);

                    $this->database->table("media")->insert(array(
                        'name' => $fileName,
                        'pages_id' => $values["page_id"],
                        'filesize' => $fileSize,
                        'file_type' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
                    ));
                }

            }
        }

        exit();
    }

    protected function createComponentLangSelector()
    {
        $control = new \LangSelectorControl($this->database);
        return $control;
    }

    function handlePublic()
    {
        $page = $this->database->table("pages")->get($this->presenter->getParameter("id"));

        if ($page->public == 1) {
            $show = 0;
        } else {
            $show = 1;
        }
        $this->database->table("pages")->get($this->presenter->getParameter("id"))->update(array("public" => $show));

        $this->presenter->redirect(this, array("id" => $this->presenter->getParameter("id"), "l" => $this->presenter->getParameter("l")));
    }

    /**
     * Toggle display
     */
    function handleToggle()
    {
        setcookie("editortype", $this->getParameter("editortype"), time() + 15552000);

        $this->presenter->redirect(this, array("id" => $this->getParameter("id")));
    }


    /************************************/

    public function purify($dirtyHtml)
    {
        return $this->htmlPurifier->purify($dirtyHtml);
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->editortype = $_COOKIE["editortype"];

        $template->pages = $this->database->table("pages")->where("NOT id", $this->presenter->getParameter("id"));
        $template->page = $this->database->table("pages")->get($this->presenter->getParameter("id"));

        $template->templates = $this->database->table("pages_templates")->where("pages_types_id IS NULL")->order("title");

        if ($this->presenter->template->member->users_roles->pages_document) {
            $template->enabled = true;
        } else {
            $template->enabled = false;
        }

        $template->page_id = $this->presenter->getParameter("id");

        $template->setFile(__DIR__ . '/EditorControl.latte');

        $template->render();
    }

}