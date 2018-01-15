<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Database\Context;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Helpdesk
{

    /** @var \Nette\Database\Context */
    private $database;

    private $mailer;

    public $id;
    public $settings;
    public $params;
    public $email;

    public function __construct(Context $database, IMailer $mailer)
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
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow
     */
    public function getInfo()
    {
        $helpdesk = $this->database->table('helpdesk')->get($this->id);

        return $helpdesk;
    }

    /**
     * Send information to all e-mails
     */
    public function send()
    {
        $info = $this->getInfo();
        $emails = $info->related('helpdesk_emails', 'helpdesk_id');
        $templateId = 1;
        $email = $this->getEmail();

        foreach ($emails as $item) {
            if ($item->email !== null) {
                $email = $item->email;
            }

            if ($item->helpdesk_templates_id) {
                $templateId = $item->helpdesk_templates_id;
            }

            $this->fillEmail($email, $item->subject, $item->body, $templateId, $this->getParams(), $item->log);
        }
    }

    /**
     * Sending the mail
     * @param $email
     * @param $subject
     * @param $body
     * @param $templateId
     * @param $params
     * @param $log
     */
    private function fillEmail($email, $subject, $body, $templateId, $params, $log)
    {
        $latte = new Engine();
        $latte->setLoader(new StringLoader());

        $emailMessage = $latte->renderToString($this->renderBody($subject, $body, $templateId), $params);

        $mail = new Message();
        $mail->setFrom($this->getSettings()['contacts:email:hq']);
        $mail->addTo($email);
        $mail->setHTMLBody($emailMessage);
        $this->mailer->send($mail);

        if ($log) {
            $this->log($email, $emailMessage);
        }
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

        $headParse = str_replace('%TITLE%', $subjectContent, $templateBody);
        $bodyParse = str_replace('%CONTENT%', $bodyContent, $headParse);

        return $bodyParse;
    }

    /**
     * Loging information
     * @param $email
     * @param $emailMessage
     */
    public function log($email, $emailMessage)
    {

        $this->database->table('helpdesk_messages')->insert(array(
            'message' => $emailMessage,
            'helpdesk_id' => $this->getId(),
            'email' => $email,
            'ipaddress' => $this->getParams()['ipaddress'],
            'date_created' => $this->getParams()['time'],
        ));
    }

}
