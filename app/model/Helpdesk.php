<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Helpdesk
{

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database, \Nette\Mail\IMailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /**
     * Sets identificator for the helpdesk table
     */
    function setId($id)
    {
        $this->id = $id;
    }

    function getId()
    {
        return $this->id;
    }

    /**
     * Sets customer e-mail. Other e-mails are set in database
     */

    function setEmail($email)
    {
        $this->email = $email;
    }

    function getEmail()
    {
        return $this->email;
    }

    function setSettings($settings)
    {
        $this->settings = $settings;
    }


    function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets parameters for e-mail template
     */
    function setParams($params)
    {
        $this->params = $params;
    }

    function getParams()
    {
        $params = $this->params;

        $params["ipaddress"] = getenv('REMOTE_ADDR');
        $params["time"] = date("Y-m-d H:i");
        $params["settings"] = $this->getSettings();
        $params["email"] = $this->getEmail();

        return $params;
    }


    /*
     * Get information about helpdesk
     */
    function getInfo()
    {
        $helpdesk = $this->database->table("helpdesk")->get($this->id);

        return $helpdesk;
    }

    /**
     * Send information to all e-mails
     */
    function send()
    {
        $info = $this->getInfo();
        $emails = $info->related('helpdesk_emails', 'helpdesk_id');

        foreach ($emails as $item) {

            if ($item->email == null) {
                $email = $this->getEmail();
            } else {
                $email = $item->email;
            }

            if ($this->helpdesk_templates_id) {
                $templateId = $this->helpdesk_templates_id;
            } else {
                $templateId = 1;
            }

            $this->fillEmail($email, $item->subject, $item->body, $templateId, $this->getParams(), $item->log);
        }
    }

    function fillEmail($email, $subject, $body, $templateId, $params, $log)
    {
        $latte = new \Latte\Engine;
        $latte->setLoader(new \Latte\Loaders\StringLoader());

        $emailMessage = $latte->renderToString($this->renderBody($subject, $body, $templateId), $params);

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->getSettings()["contacts:email:hq"]);
        $mail->addTo($email);
        $mail->setHTMLBody($emailMessage);

        $this->mailer->send($mail);

        if ($log) {
            $this->log($email, $emailMessage);
        }
    }

    function renderBody($subjectContent, $bodyContent, $templateId)
    {
        $templateBody = $this->database->table("helpdesk_templates")->get($templateId)->document;

        $headParse = str_replace("%TITLE%", $subjectContent, $templateBody);
        $bodyParse = str_replace("%CONTENT%", $bodyContent, $headParse);

        return $bodyParse;
    }

    function log($email, $emailMessage)
    {

        $this->database->table("helpdesk_messages")->insert(array(
            "message" => $emailMessage,
            "helpdesk_id" => $this->getId(),
            "email" => $email,
            "ipaddress" => $this->getParams()["ipaddress"],
            "date_created" => $this->getParams()["time"],
        ));
    }

}
