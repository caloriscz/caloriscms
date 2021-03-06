<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\IRow;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Helpdesk
{

    private Explorer $database;

    private $mailer;

    public $id;
    public $settings;
    public $params;
    public $email;

    public function __construct(Explorer $database, Mailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /**
     * Sets identificator for the helpdesk table
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets customer e-mail. Other e-mails are set in database
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }


    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets parameters for e-mail template
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        $params = $this->params;

        $params['ipaddress'] = getenv('REMOTE_ADDR');
        $params['time'] = date('Y-m-d H:i');
        $params['settings'] = $this->getSettings();
        $params['email'] = $this->getEmail();

        return $params;
    }

    /**
     * Get information about helpdesk
     * @return bool|mixed|ActiveRow|IRow
     */
    public function getInfo()
    {
        return $this->database->table('helpdesk')->get($this->id);
    }

    /**
     * Send information to all e-mails
     */
    public function send()
    {
        $info = $this->getInfo();
        $templateId = 1;
        $email = $this->getEmail();

        if ($info->email !== null) {
            $email = $info->email;
        }

        if ($info->helpdesk_templates_id) {
            $templateId = $info->helpdesk_templates_id;
        }

        $send = $this->fillEmail($email, $info->subject, $info->body, $templateId, $this->getParams(), $info->log);

        return $send;
    }

    /**
     * Sending the mail
     * @param $email
     * @param $subject
     * @param $body
     * @param $templateId
     * @param $params
     * @param $log
     * @return bool
     */
    private function fillEmail($email, $subject, $body, $templateId, $params, $log): bool
    {
        $latte = new Engine();
        $latte->setLoader(new StringLoader());

        $emailMessage = $latte->renderToString($this->renderBody($subject, $body, $templateId), $params);

        try {
            $mail = new Message();
            $mail->setFrom($this->getSettings()['contacts:email:hq']);
            $mail->addTo($email);
            $mail->setHtmlBody($emailMessage);
            $this->mailer->send($mail);
            $status = 1;
            $send = true;
        } catch (\Exception $e) {
            $status = 2;
            $send = false;
        }

        if ($log) {
            $this->log($email, $emailMessage, $status);
        }

        return $send;
    }

    /**
     *
     * @param $subjectContent
     * @param $bodyContent
     * @param $templateId
     * @return mixed
     */
    public function renderBody($subjectContent, $bodyContent, $templateId)
    {
        $templateBody = $this->database->table('helpdesk_templates')->get($templateId)->document;
        return str_replace(['%TITLE%', '%CONTENT%'], [$subjectContent, $bodyContent], $templateBody);
    }

    /**
     * Loging information
     * @param $email
     * @param $emailMessage
     */
    public function log($email, $emailMessage, $status)
    {
        $this->database->table('helpdesk_messages')->insert([
            'message' => $emailMessage,
            'helpdesk_id' => $this->getId(),
            'email' => $email,
            'ipaddress' => $this->getParams()['ipaddress'],
            'date_created' => $this->getParams()['time'],
            'status' => $status,
        ]);
    }
}
