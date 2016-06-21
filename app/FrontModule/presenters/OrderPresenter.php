<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Cart presenter.
 */
class OrderPresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();

        // Kick user from order if he has not added anything to cart
        if ($this->template->cartItems == 0) {
            $this->flashMessage("V košíku nemáte žádné zboží", "error");
            $this->redirect(":Front:Cart:default");
        }

        // Check if shipping, payment, is set

        $orderDb = $this->database->table("orders")->where("uid", session_id());
        $arr = array();
        if ($orderDb->count() > 0) {
            $order = $orderDb->fetch();

            if ($order->store_settings_shipping_id == null) {
                $arr["store_settings_shipping_id"] = 3;
            }

            if ($order->store_settings_payments_id == null) {
                $arr["store_settings_payments_id"] = 6;
            }

            if ($this->user->isLoggedIn() && $order->contacts_id == null) {
                $addressDb = $this->database->table("contacts")->where("users_id", $this->presenter->user->getId());

                if ($addressDb->count() > 0) {

                    $arr["contacts_id"] = $addressDb->fetch()->id;
                }
            }

            if (count($arr) > 0) {
                $this->database->table("orders")->where("uid", session_id())->update($arr);
            }
        }

    }


    protected function createComponentDeliveryControl()
    {
        $control = new \DeliveryControl($this->database);
        return $control;
    }

    protected function createComponentAddressControl()
    {
        $control = new \AddressControl($this->database);
        return $control;
    }

    protected function createComponentProductItems()
    {
        $control = new \ProductItemsControl($this->database);
        return $control;
    }

    protected function createComponentAddressLabel()
    {
        $control = new \AddressLabelControl($this->database);
        return $control;
    }

    /**
     * Finish order request
     */
    function createComponentFinishOrderForm()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addCheckbox('agree')
            ->addCondition($form::EQUAL, TRUE)
            ->toggle('agree-container');
        $form->addSubmit("submitm", "Dokončit objednávku")
            ->setAttribute("class", "btn btn-success btn-lg");

        $form->onValidate[] = $this->validateOrderFormSucceeded;
        $form->onSuccess[] = $this->finishOrderFormSucceeded;
        return $form;
    }

    function validateOrderFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->agree == FALSE) {
            $this->flashMessage($this->translator->translate('messages.sign.MustAgreeWithConditions'), "error");
            $this->redirect(":Front:Order:summary");
        }

        if ($this->template->cartItems <= 0) {
            $this->flashMessage($this->translator->translate('messages.sign.NoItemsInCart'), "error");
            $this->redirect(":Front:Order:summary");
        }
    }

    function finishOrderFormSucceeded()
    {
        $orderDb = $this->database->table("orders")->where(array("uid" => session_id()));

        if ($orderDb->count() > 0) {
            $order = $orderDb->fetch();
        }

        $latte = new \Latte\Engine;
        $latte->setLoader(new \Latte\Loaders\StringLoader());

        $pickup = $this->database->table("contacts")->get($order->pickups_contacts_id);

        // Shipping price
        $delivery = new Model\Store\DeliveryCalaculator($this->database);
        $delivery->setSettings($this->template->settings);
        $delivery->setOrder($order);

        $orderUpdateArr = array(
            "order_created" => date('Y-m-d H.i:s'),
            "orders_states_id" => 1,
            "shipping_price" => $delivery->calculateShipping(),
            "payment_price" => $delivery->calculatePayment(),
            "uid" => null,
        );

        // Order identifier when store:order:generateIdsOnOrderConfirm is set to 1, order id is immediately generated

        if ($this->template->settings['store:order:generateIdsOnOrderConfirm']) {
            $oid = new Model\Store\OrderIdentifier($this->database);
            $orderUpdateArr["oid"] - $orderId = $oid->setId($order->id);
        }

        $this->database->table("orders")->where(array("uid" => session_id()))->update($orderUpdateArr);

        // PDF confirmation for admin
        $file = substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Homepage';

        $residencyDb = $this->database->table("contacts")->get($this->template->settings['contacts:residency:contacts_id']);

        if ($residencyDb) {
            $residency = $residencyDb;
        } else {
            $residency = false;
        }

        $params = array(
            'order' => $order,
            'shipping' => $order->shipping_price,
            'settings' => $this->template->settings,
            'pickup' => $pickup
        );

        $paramsMail = array(
            'order' => $order,
            'residency' => $residency,
            'settings' => $this->template->settings,
            'pickup' => $pickup
        );

        $latteMail = new \Latte\Engine;
        $template = $latteMail->renderToString($file . "/components/invoice.latte", $paramsMail);

        $pdf = new \Joseki\Application\Responses\PdfResponse($template);
        $savedFile = $pdf->save(APP_DIR . '/files/invoices/', 'invoice-' . $order->oid . '');

        $support = $this->database->table("helpdesk")->get(2);
        $support_customer = $support->related("helpdesk_emails", "helpdesk_id")->get(3);
        $support_admin = $support->related("helpdesk_emails", "helpdesk_id")->get(4);

        // Send to customer
        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->template->settings["contacts:email:hq"]);
        $mail->addTo($order->email);
        $mail->setHTMLBody($latte->renderToString($support_customer->body, $params));

        $this->mailer->send($mail);

        // Send to admin

        $mailA = new \Nette\Mail\Message;
        $mailA->setFrom($this->template->settings["contacts:email:hq"]);
        $mailA->addTo($this->template->settings["contacts:email:hq"]);
        $mailA->addAttachment($savedFile);
        $mailA->setHTMLBody($latte->renderToString($support_admin->body, $params));

        $arr = array(
            "subject" => $support_customer->subject,
            "email" => $order->email,
            "message" => $latte->renderToString($support_customer->body, $params),
            "ipaddress" => getenv('REMOTE_ADDR'),
            "helpdesk_id" => 2,
            'date_created' => date("Y-m-d H:i"),
        );

        $this->database->table("helpdesk_messages")
            ->insert($arr);

        $this->mailer->send($mailA);

        $this->flashMessage("Objednávka úspěšně dokončena", "success");
        $this->redirect(":Front:OrderSuccess:default", array(
            "transid" => 'TR' . Nette\Utils\Random::generate(4, 'A-Z') . str_pad($order->id, 6, '0', STR_PAD_LEFT),
        ));
    }

    /**
     * Insert bonus
     */
    function handleAddBonus($id)
    {
        $bonus = new Model\Store\Bonus($this->database);
        $bonus->setUser($this->user->getId());
        $bonus->setCartTotal($this->template->cartTotal);

        if ($this->template->cartId == false) {
            $this->flashMessage($this->translator->translate('messages.sign.NoItemsBoughtYet'), "error");
            $this->redirect(":Front:Cart:default");
        } elseif (!$bonus->isEligible($id)) {
            $this->flashMessage("Zatím nemáte nárok na bonus", "error");
            $this->redirect(":Front:Order:delivery");
        } else {
            $this->database->table("orders")
                ->get($this->template->cartId)
                ->update(array(
                    "store_bonus_id" => $id,
                ));
        }

        $this->redirect(":Front:Order:delivery");
    }

    function renderBonus()
    {
        $bonus = new Model\Store\Bonus($this->database);
        $bonus->setUser($this->user->getId());
        $bonus->setCartTotal($this->template->cartTotal);

        $this->template->database = $this->database;
        $this->template->bonuses = $bonus->getBonuses();
    }

    function renderSummary()
    {
        $this->template->info = $this->template->cartInfo;
        $billingAddress = $this->template->cartObject->getAddress($this->template->cartInfo->contacts_id);
        $this->template->deliveryAddress = $this->template->cartObject->getAddress($this->template->cartInfo->delivery_contacts_id);
        $this->template->shippingByWeight = $this->database->table("store_settings_weights");

        if ($this->user->isLoggedIn()) {
            $bonus = new Model\Store\Bonus($this->database);
            $bonus->setUser($this->user->getId());
            $bonus->setCartTotal($this->template->cartTotal);

            $bonusId = $this->template->info->store_bonus_id;

            if ($this->template->cartId == false) {
                $this->flashMessage("Zatím jste nenakoupili žádné zboží", "error");
                $this->redirect(":Front:Cart:default");
            }

            if ($bonusId != null) {
                if ($bonus->isEligible($bonusId) == false && $bonus->isEligibleForBonus($this->template->member->categories_id)) {
                    $this->flashMessage("Cena se změnila. Můžete znovu vybrat dárek", "note");
                    $this->redirect(":Front:Order:bonus");
                } else {
                    $this->template->cartObject->removeBonus();
                }
            } else {
                if ($bonus->isEligibleForBonus($this->template->member->categories_id)) {
                    $this->flashMessage("Cena se změnila. Můžete si znovu vybrat dárek", "note");
                    $this->redirect(":Front:Order:bonus");
                }
            }
        }

        $this->template->billingAddress = $billingAddress;
    }

}
