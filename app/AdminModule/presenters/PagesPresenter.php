<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class PagesPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Insert page content
     */
    function createComponentInsertPageForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->getElementPrototype()->class = 'form';

        $form->addText("presenter", "Presenter");
        $form->addText("title", "URL");
        $form->addText("template", "Šablona");
        $form->addText("name", "Název stránky");

        $form->setDefaults(array(
            "presenter" => "Stranky",
        ));

        $form->onSuccess[] = $this->insertPageFormSucceeded;
        $form->addSubmit("submit", "Uložit");

        return $form;
    }

    function insertPageFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("pages")->insert(array(
            'presenter' => $form->values->presenter,
            'title' => $form->values->title,
            'template' => $form->values->template,
            'name' => $form->values->name,
        ));

        $this->redirect(":Admin:Pages:detail", array("id" => $form->values->title));
    }

    /**
     * Edit page content
     */
    function createComponentEditForm($lang)
    {
        $pages = $this->database->table("pages")->where(array("title" => $this->getParameter("id")))->fetch();
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addHidden("title");
        $form->addText("name")
                ->setAttribute("placeholder", "Nadpis");
        $form->addTextArea("body")
                ->setAttribute("class", "form-control")
                ->setHtmlId('wysiwyg');
        $form->setDefaults(array(
            "id" => $pages->id,
            "title" => $pages->title,
            "name" => $pages->name,
            "body" => $pages->body,
        ));

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->addSubmit("submit", "Uložit")
                ->setHtmlId('formxins');

        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $this->database->table("pages")->where(array("id" => $form->values->id))
                ->update(array(
                    'name' => $form->values->name,
                    'body' => $form->values->body,
        ));

        $this->redirect(":Admin:Pages:detail", array("id" => $form->values->title));
    }

    /**
     * Insert page
     */
    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("name", "[cal:t(Name) /]");

        $form->addSubmit("submit", "Vytvořit");

        return $form;
    }

    /**
     * Delete page
     */
    function deleteFormSucceeded($cols)
    {
        if (ACL::authorize("templates") == 0) {
            $msg = '[cal:t(DontHavePermissions) /]';
        } else {
            $page = new \PageEditor\Page\Control;
            $page->delete();

            IO::remove(_CALSET_PATHS_BASE . '/caloris_www/' . $_POST["name"] . '.html');

            $msg = "done";
        }

        $params = array(
            "url" => _CALSET_PATHS_URI . '/pageeditor/index.html',
            "querystring" => array(
                "msg" => $msg,
            )
        );

        return $params;
    }

    /**
     * Create page
     */
    function insertFormSucceeded($cols)
    {
        $params = array();
        $columns["short"] = Strings::webalize($_POST["name"], '-');

        if (ACL::authorize("templates") == 0) {
            $params["querystring"]["msg"] = '[cal:t(DontHavePermissions) /]';
        }

        if ($cols["name"] == 'administrator') {
            $params["querystring"]["msg"] = '[cal:t(ThisNameIsReserved;pageeditor) /]';
        }

        if (strlen($params["querystring"]["msg"]) == 0 && $cols["type"] == 1) {
            $page = new \PageEditor\Page\Control;
            $xmlReady = $page->insert($cols);
        }

        if (strlen($params["querystring"]["msg"]) == 0) {
            $url = _CALSET_PATHS_URI . '/pageeditor/pages.properties.html';
            $file = _CALSET_PATHS_BASE . '/caloris_www/templates/' . $columns["short"] . '.html';
            \Caloris\IO::file($file, $xmlReady);
        } else {
            $url = _CALSET_PATHS_URI . '/pageeditor/pages.properties.html';
        }

        $params["url"] = $url;
        $params["querystring"]["name"] = $columns["short"] . ".html";

        return $params;
    }

    public function renderDefault()
    {
        $this->template->pages = $this->database->table("pages")->order("title");
    }

    public function renderDetail()
    {
        $this->template->pages = $this->database->table("pages")->where(array("title" => $this->getParameter("id")))->fetch();
    }

}
