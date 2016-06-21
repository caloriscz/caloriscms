<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Link management with categories
 */
class TestPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->link = $this->database->table("links")
                ->get($this->getParameter("id"));
    }

    function createComponentTestForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal ajax";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText('test', "Test");
        $form->addSubmit("submitm", "Vytvořit")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->testFormSucceeded;
        return $form;
    }

    function testFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->isAjax()) {
            //$form->setValues(array('test' => 777), TRUE);
            $this->template->articles = $form->values->test;
            $this->template->test = 'žeby';
            $form->values->test = 54;
            $this->invalidateControl();
            $this->redrawControl('header');
            $this->redrawControl('form');
            $this->payload->alert = 'Vaše data byla uložena.';
        }

        //$this->redirect(":Admin:Links:detail", array("id" => $id));
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->getElementPrototype()->class = "ajax form-horizontal";
        $form->addText("url", "URL");
        $form->addSubmit("submitm", "Vytvořit")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->isAjax()) {
            $this->payload->message = 'Success';
            $this->invalidateControl();
            $this->redrawControl('header');
        }


        /* $id = $this->database->table("links")
          ->insert(array(
          "categories_id" => $this->template->settings['categories:id:link'],
          )); */

        //$this->redirect(":Admin:Links:detail", array("id" => $id));
    }

    public function renderDefault()
    {
        $hash = hash_hmac("sha256", "", "my-secret-api-key");

        $file = "http://caloris.dev:8080/api/v1/crud/1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-AUTH-TOKEN:' . $hash));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($ch);
        curl_close($ch);
        echo $contents;
    }

}
