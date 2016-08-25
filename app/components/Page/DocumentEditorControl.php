<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;

class DocumentEditorControl extends Control
{

    private $htmlPurifier;

    /** @var Nette\Database\Context */
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
            ->setHtmlId('wysiwyg-page')
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

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->onValidate[] = $this->permissionValidated;
        $form->addSubmit("submit", "dictionary.main.Save")
            ->setHtmlId('formxins');

        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form) {
        if ($this->template->member->users_roles->pages_edit == 0) {
            $this->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new \App\Model\Document($this->database);
        $doc->setLanguage($form->values->l);

        //$document = $this->purify($form->values->document);

        $doc->setDocument($form->values->document);
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
                    // thumbnails
                    $image = \Nette\Utils\Image::fromFile($destination);
                    $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
                    $image->sharpen();
                    $image->save(APP_DIR . '/media/' . $values["page_id"] . '/tn/' . $fileName);
                    chmod(APP_DIR . '/media/' . $values["page_id"] . '/tn/' . $fileName, 0644);

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

    /************************************/

    public function purify($dirtyHtml)
    {
        return $this->htmlPurifier->purify($dirtyHtml);
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        if ($this->presenter->template->member->users_roles->pages_document) {
            $template->enabled = true;
        } else {
            $template->enabled = false;
        }

        $template->page_id = $this->presenter->getParameter("id");

        $template->setFile(__DIR__ . '/DocumentEditorControl.latte');

        $template->render();
    }

}