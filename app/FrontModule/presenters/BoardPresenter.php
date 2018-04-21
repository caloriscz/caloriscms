<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class BoardPresenter extends BasePresenter
{

    /**
     * Insert question request
     */
    function createComponentQuestionForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal typePH";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("name")
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zvolte jméno s alespoň %d znaky', 2)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte jméno s nejvýše %d znaky', 35)
            ->setAttribute("placeholder", "messages.helpdesk.name")
            ->setAttribute("class", "input-change");
        $form->addText("email")
            ->setAttribute("placeholder", "messages.helpdesk.email")
            ->setAttribute("class", "input-change")
            ->setOption("description", 1)
            ->addCondition(Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::EMAIL, 'Zadejte platný email.');
        $form->addText("phone")
            ->setAttribute("placeholder", "messages.helpdesk.phone")
            ->setAttribute("class", "input-change")
            ->setOption("description", 1);
        $form->addText("subject")
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Vyplňte předmět zprávy', 2)
            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Zvolte kratší předmět zprávy (nejvýše %d znaků)', 300)
            ->setAttribute("placeholder", "Předmět")
            ->setAttribute("class", "input-change form-control");
        $form->addTextArea("message")
            ->setAttribute("placeholder", "messages.helpdesk.message")
            ->setAttribute("class", "input-change form-control");


        $name = $this->getParameter("name");
        $email = $this->getParameter("email");
        $phone = $this->getParameter("phone");

        $form->setDefaults(array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "subject" => $this->getParameter("subject"),
            "message" => $this->getParameter("message"),
        ));
        $form->addSubmit("submitm", "messages.helpdesk.send")
            ->setAttribute("class", "btn btn-inverse btn-lg btn btn-default btn-eshop");

        $form->onValidate[] = $this->questionFormValidated;
        $form->onSuccess[] = $this->questionFormSucceeded;

        return $form;
    }

    function questionFormValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        $cols = array(
            "name" => $form->values->name,
            "subject" => $form->values->subject,
            "phone" => $form->values->phone,
            "email" => $form->values->email,
            "message" => $form->values->mesage
        );

        if (strlen($form->values->name) != strlen(strip_tags($form->values->name))) {
            unset($cols["name"]);
            $this->flashMessage("Jméno obsahuje nepovolené znaky (adresa, odkaz)", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        if (strlen($form->values->message) != strlen(strip_tags($form->values->message))) {
            unset($cols["message"]);
            $this->flashMessage("Zpráva obsahuje nepovolené znaky (adresa, odkaz)", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        $blacklist = $this->database->table("blacklist")->fetchPairs("id", "title");

        if (Model\Arrays::strpos($form->values->message, $blacklist) === null) {
        } else {
            unset($cols["message"]);
            $this->flashMessage("Zpráva obsahuje nepovolená slova", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        if (Model\Arrays::strpos($form->values->subject, $blacklist) === null) {
        } else {
            unset($cols["subject"]);
            $this->flashMessage("Předmět obsahuje nepovolená slova", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        if (Model\Arrays::strpos($form->values->name, $blacklist) === null) {
        } else {
            unset($cols["name"]);
            $this->flashMessage("Jméno obsahuje nepovolená slova", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }


        if (strlen($form->values->name) < 1) {
            unset($cols["name"]);
            $this->flashMessage("Vyplňte Vaše skutečné jméno", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        if (strlen($form->values->email) > 0 && \Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->flashMessage("Vyplňte pravý mail", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }

        if (strlen($form->values->message) < 2) {
            unset($cols["message"]);
            $this->flashMessage("Vyplňte zprávu", "error");
            $this->redirect(":Front:Otazky:default", $cols);
        }
    }

    function questionFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {

        if ($this->template->member->username == 'admin') {
            $name = 'admin';
        } elseif ($this->template->member->username == 'skola') {
            $name = 'Ředitelka';
        } else {
            $name = $this->getParameter("name");
        }

        $arrData = array(
            "subject" => $form->values->subject,
            "email" => $form->values->email,
            "author" => $form->values->name,
            "body" => $form->values->message,
            "ipaddress" => getenv('REMOTE_ADDR'),
            'date_created' => date("Y-m-d H:i:s"),
        );

        $this->database->table("board")
            ->insert($arrData);

        $this->flashMessage("Zpráva odeslána", "note");
        $this->redirect(":Front:Otazky:default");
    }

    /**
     * Answer question request
     */
    function createComponentAnswerForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal typePH";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("parent");
        $form->addText("name")
            ->setAttribute("placeholder", "messages.helpdesk.name")
            ->setAttribute("class", "input-change");
        $form->addText("email")
            ->setAttribute("placeholder", "messages.helpdesk.email")
            ->setAttribute("class", "input-change")
            ->setOption("description", 1)
            ->addCondition(Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::EMAIL, 'Zadejte platný email.');
        $form->addText("phone")
            ->setAttribute("placeholder", "messages.helpdesk.phone")
            ->setAttribute("class", "input-change")
            ->setOption("description", 1);
        $form->addTextArea("message")
            ->setAttribute("placeholder", "messages.helpdesk.message")
            ->setAttribute("style", "height: 250px;")
            ->setAttribute("class", "input-change form-control");

        if ($this->template->member->username == 'admin') {
            $name = 'admin';
            $email = 'caloris@caloris.cz';
            $phone = '+420 723 541 167';
        } elseif ($this->template->member->username == 'skola') {
            $name = 'Ředitelka';
            $email = 'spurna@skola-praktik.cz';
            $phone = '+420 585 228 256';
        } else {
            $name = $this->getParameter("name");
            $email = $this->getParameter("email");
            $phone = $this->getParameter("phone");
        }

        $form->setDefaults(array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "message" => $this->getParameter("message"),
            "parent" => $this->getParameter("id"),
        ));

        $form->addSubmit("submitm", "messages.helpdesk.send")
            ->setAttribute("class", "btn btn-inverse btn-lg btn btn-default");

        $form->onValidate[] = $this->answerFormValidated;
        $form->onSuccess[] = $this->answerFormSucceeded;
        return $form;
    }

    function answerFormValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        $cols = array(
            "name" => $form->values->name,
            "phone" => $form->values->phone,
            "email" => $form->values->email,
            "message" => $form->values->message,
            "id" => $form->values->parent,
        );

        if (strlen($form->values->name) != strlen(strip_tags($form->values->name))) {
            unset($cols["name"]);
            $this->flashMessage("Název obsahuje nepovolené znaky (adresa, odkaz)", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        if (strlen($form->values->subject) != strlen(strip_tags($form->values->subject))) {
            unset($cols["subject"]);
            $this->flashMessage("Předmět obsahuje nepovolené znaky (adresa, odkaz)", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        if (strlen($form->values->message) != strlen(strip_tags($form->values->message))) {
            unset($cols["message"]);
            $this->flashMessage("Zpráva obsahuje nepovolené znaky (adresa, odkaz)", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        $blacklist = $this->database->table("blacklist")->fetchPairs("id", "title");

        if (Model\Arrays::strpos($form->values->message, $blacklist) === null) {
        } else {
            unset($cols["message"]);
            $this->flashMessage("Zpráva obsahuje nepovolená slova", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        if (Model\Arrays::strpos($form->values->name, $blacklist) === null) {
        } else {
            unset($cols["name"]);
            $this->flashMessage("Jméno obsahuje nepovolená slova", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }


        if (strlen($form->values->name) < 1) {
            unset($cols["name"]);
            $this->flashMessage("Vyplňte Vaše skutečné jméno", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        if (strlen($form->values->email) > 0 && \Nette\Utils\Validators::isEmail($form->values->email) == FALSE) {
            $this->flashMessage("Vyplňte pravý mail", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }

        if (strlen($form->values->message) < 2) {
            unset($cols["message"]);
            $this->flashMessage("Vyplňte zprávu", "error");
            $this->redirect(":Front:Otazky:detail", $cols);
        }
    }

    function answerFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $arrData = array(
            "parent_id" => $form->values->parent,
            "author" => $form->values->name,
            "email" => $form->values->email,
            "body" => $form->values->message,
            "ipaddress" => getenv('REMOTE_ADDR'),
            'date_created' => date("Y-m-d H:i:s"),
        );

        $this->database->table("board")
            ->insert($arrData);

        $this->flashMessage("Zpráva odeslána", "note");
        $this->redirect(":Front:Otazky:detail", array("id" => $form->values->parent));
    }

    /**
     * Delete question
     */
    function handleDeleteQuestion($id)
    {
        $this->database->table("board")->where("id = ? OR parent_id = ?", $id, $id)->delete();

        $this->redirect(":Front:Otazky:default");
    }

    /**
     * Delete answer
     */
    function handleDeleteAnswer($id, $thread)
    {
        $this->database->table("board")->get($id)->delete();

        $this->redirect(":Front:Otazky:detail", array("id" => $thread));
    }

    public function renderDefault()
    {
        $board = $this->database->table("board")
            ->where(array("parent_id" => NULL))
            ->order("date_created DESC");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($board->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));
        $this->template->board = $board->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;

        if ($this->user->isLoggedIn) {
            $templateMember = $this->template->member->username;
        } else {
            $templateMember = null;
        }

        if ($templateMember == 'admin' or $templateMember == 'skola') {
            $this->template->canEdit = true;
        } else {
            $this->template->canEdit = false;
        }

        $this->template->database = $this->database;
    }

    public function renderDetail()
    {
        $this->template->message = $this->database->table("board")->get($this->getParameter("id"));

        $board = $this->database->table("board")
            ->where(array("parent_id" => $this->getParameter("id")))
            ->order("date_created ASC");

        $this->template->board = $board;

        if ($this->user->isLoggedIn) {
            $templateMember = $this->template->member->username;
        } else {
            $templateMember = null;
        }

        if ($templateMember == 'admin' or $templateMember == 'skola') {
            $this->template->canEdit = true;
        } else {
            $this->template->canEdit = false;
        }
    }

}