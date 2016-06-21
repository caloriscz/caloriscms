<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Helpdesk presenter.
 */
class HelpdeskPresenter extends BasePresenter
{

    function handleDelete($id)
    {
        $this->database->table("helpdesk_messages")->get($id)->delete();

        $this->redirect(this, array("id" => $this->getParameter("helpdesk")));
    }

    /**
     * Send teste-mail
     */
    function createComponentSendTestMailForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addText("email", "E-mail");
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->onSuccess[] = $this->sendTestMailFormSucceeded;
        return $form;
    }

    function sendTestMailFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $emailDb = $this->database->table("helpdesk_emails")->get($form->values->id);

        $latte = new \Latte\Engine;
        $params = array(
            'body' => "Body",
        );

        $paramsTemplate = array(
            'name' => "John Doe",
            'phone' => "+420 123 456 789",
            'email' => $this->template->settings["contacts:email:hq"],
            'address' => "42.42.42.256",
            'time' => date("j. n. Y H:i:s"),
            'message' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. "
                . "Aliquam purus lacus, tempor sit amet tincidunt ut, finibus non quam. "
                . "Quisque ac metus quis odio vestibulum consectetur congue id diam. "
                . "Sed ligula ante, suscipit sed purus ac, dignissim hendrerit nisi.<br /> "
                . "Praesent scelerisque nulla nec posuere accumsan. Etiam ac libero in "
                . "ipsum volutpat accumsan. Sed pretium sodales fermentum. Quisque sed feugiat odio. ",
        );

        $latte->setLoader(new \Latte\Loaders\StringLoader);
        $latte->renderToString(substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Emails/components/skeleton.latte', $params);
        $renderedTemplate = $latte->renderToString($emailDb->body, $paramsTemplate);

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->template->settings["site:title"] . ' <' . $this->template->settings["contacts:email:hq"] . '>')
            ->addTo($form->values->email)
            ->setSubject($emailDb->subject)
            ->setHTMLBody($renderedTemplate);

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);

        $this->redirect(':Admin:Helpdesk:emails', array("id" => $form->values->id));
    }

    /**
     * Edit helpdesk
     */
    function createComponentEditHelpdeskForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("helpdesk_id");
        $form->addText("title", "dictionary.main.Title");
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control")
            ->setAttribute("style", "height: 200px;");
        $form->addCheckbox("fill_phone");

        $helpdesk = $this->database->table("helpdesk")->get($this->getParameter("id"));

        $form->setDefaults(array(
            "helpdesk_id" => $helpdesk->id,
            "title" => $helpdesk->title,
            "description" => $helpdesk->description,
            "fill_phone" => $helpdesk->fill_phone,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save");

        $form->onSuccess[] = $this->editHelpdeskFormSucceeded;
        return $form;
    }

    function editHelpdeskFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table("helpdesk")->get($form->values->helpdesk_id)
            ->update(array(
                "title" => $form->values->title,
                "description" => $form->values->description,
                "fill_phone" => $form->values->fill_phone,
            ));

        $this->redirect(this, array("id" => $form->values->helpdesk_id));
    }


    /**
     * E-mail template edit
     */
    function createComponentInsertTemplateForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText("template", "dictionary.main.Template");
        $form->addText("subject", "dictionary.main.Subject");
        $form->addSubmit("submitm", "dictionary.main.Insert");

        $form->onSuccess[] = $this->insertTemplateFormSucceeded;
        return $form;
    }

    function insertTemplateFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table("helpdesk_emails")
            ->insert(array(
                "template" => $form->values->template,
                "subject" => $form->values->subject,
            ));

        $this->redirect(':Admin:Helpdesk:emails', array("id" => $id));
    }

    /**
     * E-mail template edit
     */
    function createComponentEditMailForm()
    {
        $emailDb = $this->database->table("helpdesk_emails")->get($this->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->id = "search-form";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addText("subject");
        $form->addTextArea("body")
            ->setAttribute("style", "width: 500px;")
            ->setHtmlId("wysiwyg");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "subject" => $emailDb->subject,
            "body" => $emailDb->body,
        ));


        $form->onSuccess[] = $this->editCategoryFormSucceeded;
        return $form;
    }

    function editCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("helpdesk_emails")->get($form->values->id)
            ->update(array(
                "subject" => $form->values->subject,
                "body" => $form->values->body,
            ));

        $this->redirect(':Admin:Helpdesk:emails', array("id" => $form->values->id));
    }

    /**
     * Delete post
     */
    function handleDeleteTemplate($id)
    {
        $this->database->table("helpdesk_emails")->get($id)->delete();

        $this->redirect(":Admin:Helpdesk:default", array("id" => $this->getParameter("helpdesk")));
    }

    public function renderDefault()
    {
        $this->template->helpdesk = $this->database->table("helpdesk");
        $this->template->templates = $this->database->table("helpdesk_emails")->where("helpdesk_id", $this->getParameter("id"));

        if (!$this->getParameter("id")) {
            $helpdeskId = null;
        } else {
            $helpdeskId = $this->getParameter('id');
        }

        $messages = $this->database->table("helpdesk_messages")->where(array("helpdesk_id" => $helpdeskId))
            ->order("subject");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($messages->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->paginator = $paginator;
        $this->template->messages = $messages->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();


    }


    public function renderDetail()
    {
        $this->template->helpdesk = $this->database->table("helpdesk")->get($this->getParameter("id"));
        $this->template->message = $this->database->table("helpdesk_messages")->get($this->getParameter("id"));
    }
}
