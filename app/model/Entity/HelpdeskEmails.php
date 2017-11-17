<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpdeskEmails
 *
 * @ORM\Table(name="helpdesk_emails", indexes={@ORM\Index(name="helpdesk_id", columns={"helpdesk_id"}), @ORM\Index(name="helpdesk_templates_id", columns={"helpdesk_templates_id"})})
 * @ORM\Entity
 */
class HelpdeskEmails
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=40, nullable=false)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=200, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=250, nullable=false)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", length=65535, nullable=false)
     */
    private $body;

    /**
     * @var integer
     *
     * @ORM\Column(name="log", type="smallint", nullable=false)
     */
    private $log = '0';

    /**
     * @var \App\Model\Entity\Helpdesk
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Helpdesk")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="helpdesk_id", referencedColumnName="id")
     * })
     */
    private $helpdesk;

    /**
     * @var \App\Model\Entity\HelpdeskTemplates
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\HelpdeskTemplates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="helpdesk_templates_id", referencedColumnName="id")
     * })
     */
    private $helpdeskTemplates;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return HelpdeskEmails
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return HelpdeskEmails
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return HelpdeskEmails
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return HelpdeskEmails
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set log
     *
     * @param integer $log
     *
     * @return HelpdeskEmails
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return integer
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set helpdesk
     *
     * @param \App\Model\Entity\Helpdesk $helpdesk
     *
     * @return HelpdeskEmails
     */
    public function setHelpdesk(\App\Model\Entity\Helpdesk $helpdesk = null)
    {
        $this->helpdesk = $helpdesk;

        return $this;
    }

    /**
     * Get helpdesk
     *
     * @return \App\Model\Entity\Helpdesk
     */
    public function getHelpdesk()
    {
        return $this->helpdesk;
    }

    /**
     * Set helpdeskTemplates
     *
     * @param \App\Model\Entity\HelpdeskTemplates $helpdeskTemplates
     *
     * @return HelpdeskEmails
     */
    public function setHelpdeskTemplates(\App\Model\Entity\HelpdeskTemplates $helpdeskTemplates = null)
    {
        $this->helpdeskTemplates = $helpdeskTemplates;

        return $this;
    }

    /**
     * Get helpdeskTemplates
     *
     * @return \App\Model\Entity\HelpdeskTemplates
     */
    public function getHelpdeskTemplates()
    {
        return $this->helpdeskTemplates;
    }
}
