<?php
namespace Caloriscz\Helpdesk;

use Nette\Application\UI\Control;

class SendTestMailControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send teste-mail
     */
    function createComponentSendForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $form->addHidden("id");
        $form->addText("email", "E-mail");
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults(array(
            "id" => $this->presenter->getParameter("id"),
        ));

        $form->onSuccess[] = $this->sendFormSucceeded;
        return $form;
    }

    function sendFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $emailDb = $this->database->table("helpdesk_emails")->get($form->values->id);

        $latte = new \Latte\Engine;
        $params = array(
            'body' => "Body",
        );

        $paramsTemplate = array(
            'name' => "John Doe",
            'phone' => "+420 123 456 789",
            'email' => $this->presenter->template->settings["contacts:email:hq"],
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
        $renderedTemplate = $latte->renderToString($emailDb->body, $paramsTemplate);

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->presenter->template->settings["site:title"] . ' <' . $this->presenter->template->settings["contacts:email:hq"] . '>')
            ->addTo($form->values->email)
            ->setSubject($emailDb->subject)
            ->setHTMLBody($renderedTemplate);

        $this->presenter->mailer->send($mail);

        $this->presenter->redirect(this, array("id" => $form->values->id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SendTestMailControl.latte');

        $template->render();
    }

}