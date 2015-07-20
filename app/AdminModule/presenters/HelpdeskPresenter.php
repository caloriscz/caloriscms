<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HelpdeskPresenter extends BasePresenter
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

    public function renderDefault()
    {
        $this->template->helpdesk = $this->database->table("helpdesk")->order("subject");
    }

    /**
     * Send advanced
     */
    function sendFormSucceeded()
    {
        $xml = simplexml_load_file(_CALSET_PATHS_BASE . '/caloris_helpdesk/data/module.xml');

        $cols["name"] = filter_input(INPUT_POST, "name");
        $cols["company"] = filter_input(INPUT_POST, "company");
        $cols["email"] = filter_input(INPUT_POST, "email");
        $cols["phone"] = filter_input(INPUT_POST, "phone");
        $cols["vatin"] = filter_input(INPUT_POST, "vatin");
        $cols["description"] = filter_input(INPUT_POST, "description");

        $url = filter_input(INPUT_POST, "return");

        if (strlen($cols["name"]) > 120) {
            $msg = "[cal:www.t(NameTooLong;helpdesk) /]";
            $cols["name"] = NULL;
        } elseif (strlen($cols["name"]) < 4 and $xml->request->name_optional == 1) {
            $msg = "[cal:www.t(NameTooShort;helpdesk) /]";
            $cols["name"] = NULL;
        }

        if (strlen($cols["company"]) > 120 && $xml->request->company_optional == 0) {
            $msg = "[cal:www.t(CompanyNameTooLong) /]";
            $cols["company"] = NULL;
        } elseif (strlen($cols["company"]) < 3 && $xml->request->company_optional == 1) {
            $msg = "[cal:www.t(CompanyNameTooShort;helpdesk) /]";
            $cols["company"] = NULL;
        }

        if (\Nette\Utils\Validators::isEmail($cols["email"]) == false) {
            $msg = "[cal:www.t(InvalidEmailAddress;helpdesk) /]";
            $cols["email"] = NULL;
        }

        if (strlen($cols["phone"]) > 18) {
            $msg = "[cal:www.t(PhoneTooLong;helpdesk) /]";
            $cols["phone"] = NULL;
        } elseif (strlen($cols["phone"]) < 7 && $xml->request_phone_optional == 1) {
            $msg = "[cal:www.t(PhoneTooShort;helpdesk) /]";
            $cols["phone"] = NULL;
        }

        if (strlen($cols["vatin"]) > 10) {
            $msg = "[cal:www.t(VATINTooLong;helpdesk) /]";
            $cols["vatin"] = NULL;
        } elseif (strlen($cols["vatin"]) < 8 && $xml->request->vatin_optional == 1) {
            $msg = "[cal:www.t(VATINTooShort;helpdesk) /]";
            $cols["vatin"] = NULL;
        }

        if (strlen($cols["description"]) > 500) {
            $msg = "[cal:www.t(descriptionTooLong;helpdesk) /]";
            $cols["description"] = NULL;
        } elseif (strlen($cols["description"]) < 3 && $xml->request->description_optional == 1) {
            $msg = "[cal:www.t(descriptionTooShort;helpdesk) /]";
            $cols["description"] = NULL;
        }

        $areasPost = $_POST["areas"];

        if (count($areasPost) > 0) {
            $groupsArr = explode(";", $xml->request->areas);

            $areas = '[cal:www.t(InterestedIn;helpdesk) /]:<br />';

            foreach ($areasPost as $key => $value) {
                if ($groupsArr[$key]) {
                    $areas .= $groupsArr[$key] . '<br />';
                }
            }
        }

        $logger = new \Helpdesk\Request\Process($dbOrm);

        if ($logger->check($cols) == FALSE) {
            $msg = '[cal:www.t(RequestErrorMessage;helpdesk) /]';
        }

        if ($msg == '') {
            if ($xml->request->log == 1) {
                $logger->log($cols);
            }

            $masks = array(
                "%name%" => $cols["name"],
                "%company%" => $cols["company"],
                "%email%" => $cols["email"],
                "%phone%" => $cols["phone"],
                "%vatin%" => $cols["vatin"],
                "%description%" => $cols["description"],
                "%ipaddress%" => $_SERVER["REMOTE_ADDR"],
                "%date%" => date("Y-m-d"),
                "%time%" => date("H:i"),
                "%areas%" => $areas,
            );

            $path = _CALSET_PATHS_BASE . '/caloris_helpdesk/binaries/masks/';
            $requestAdmin = \Caloris\IO::get($path . 'request-admin.html');
            $requestClient = \Caloris\IO::get($path . 'request-client.html');

            $maskAdmin .= \Caloris\Tools::strReplaceAssoc($masks, $requestAdmin);
            $maskClient .= \Caloris\Tools::strReplaceAssoc($masks, $requestClient);

            $mail = new \Nette\Mail\Message;
            $mail->setFrom(_CALSET_BASIC_ADMIN_EMAIL);
            $mail->addTo(_CALSET_BASIC_ADMIN_EMAIL);
            $mail->setSubject(_CALSET_BASIC_TITLE . ": [cal:www.t(Request;helpdesk) /]");
            $mail->setHTMLBody($maskAdmin);

            $mailA = new \Nette\Mail\Message;
            $mailA->setFrom(_CALSET_BASIC_ADMIN_EMAIL);
            $mailA->addTo($cols["email"]);
            $mailA->setSubject(_CALSET_BASIC_TITLE . ": [cal:www.t(Request;helpdesk) /]");
            $mailA->setHTMLBody($maskClient);

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
            $mailer->send($mailA);

            unset($cols);

            $msg = "[cal:www.t(RequestThanksMessage;helpdesk) /]";
        }

        $params = array(
            "url" => $url,
            "querystring" => array(
                "msg" => $msg,
                "name" => $cols["name"],
                "company" => $cols["company"],
                "email" => $cols["email"],
                "phone" => $cols["phone"],
                "vatin" => $cols["vatin"],
                "description" => $cols["description"],
            )
        );

        return $params;
    }

    /**
     * User delete
     */
    function handleDelete($id)
    {
        $this->database->table("helpdesk")->get($id)->delete();

        $this->redirect(":Admin:Helpdesk:default");
    }

}
