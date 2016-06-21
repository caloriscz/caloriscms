<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Orders presenter.
 */
class OrdersPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->shippingMethods = $this->database->table("store_settings_shipping")->where("show = 1")->fetchPairs("id", "title");
        $this->template->paymentMethods = $this->database->table("store_settings_payments")->where("show = 1")->fetchPairs("id", "title");
        $this->template->order = $this->database->table("orders")->get($this->getParameter("id"));
    }

    /**
     * Edit billing address request
     */
    public function createComponentEditBillingForm()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $contacts = $this->database->table("contacts")->get($order->contacts_id);
        }

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contact_id");
        $form->addText("name", "dictionary.main.Name")
            ->setAttribute("placeholder", "dictionary.main.Name")
            ->setAttribute("class", "form-control");
        $form->addText("company", "dictionary.main.Company")
            ->setAttribute("placeholder", "dictionary.main.Company")
            ->setAttribute("class", "form-control");
        $form->addText("street", "dictionary.main.Street")
            ->setAttribute("placeholder", "dictionary.main.Street")
            ->setAttribute("class", "form-control");
        $form->addText("city", "dictionary.main.City")
            ->setAttribute("placeholder", "dictionary.main.City")
            ->setAttribute("class", "form-control");
        $form->addText("zip", "dictionary.main.ZIP")
            ->setAttribute("placeholder", "dictionary.main.ZIP")
            ->setAttribute("class", "form-control");
        $form->addText("vatin", "dictionary.main.VatIn")
            ->setAttribute("placeholder", "dictionary.main.VatIn")
            ->setAttribute("class", "form-control");
        $form->addText("vatid", "dictionary.main.VatId")
            ->setAttribute("placeholder", "dictionary.main.VatId")
            ->setAttribute("class", "form-control");


        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "contact_id" => $contacts->id,
            "name" => $contacts->name,
            "street" => $contacts->street,
            "city" => $contacts->city,
            "zip" => $contacts->zip,
            "vatin" => $contacts->vatin,
            "vatid" => $contacts->vatid,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->editBillingFormSucceeded;

        return $form;
    }

    public function editBillingFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
            ->where(array("id" => $form->values->contact_id))
            ->update(array(
                "name" => $form->values->name,
                "company" => $form->values->company,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "vatin" => $form->values->vatin,
                "vatid" => $form->values->vatid,
            ));

        $this->redirect(":AdminStore:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Edit billing address request
     */
    function createComponentEditDeliveryAddressForm()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $contacts = $this->database->table("contacts")->get($order->delivery_contacts_id);
        }

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contact_id");
        $form->addText("name", "dictionary.main.Name")
            ->setAttribute("placeholder", "dictionary.main.Name")
            ->setAttribute("class", "form-control");
        $form->addText("company", "dictionary.main.Company")
            ->setAttribute("placeholder", "dictionary.main.Company")
            ->setAttribute("class", "form-control");
        $form->addText("street", "dictionary.main.Street")
            ->setAttribute("placeholder", "dictionary.main.Street")
            ->setAttribute("class", "form-control");
        $form->addText("city", "dictionary.main.City")
            ->setAttribute("placeholder", "dictionary.main.City")
            ->setAttribute("class", "form-control");
        $form->addText("zip", "dictionary.main.ZIP")
            ->setAttribute("placeholder", "ZIP")
            ->setAttribute("class", "form-control");
        $form->addText("phone", "dictionary.main.Phone")
            ->setAttribute("placeholder", "dictionary.main.Phone")
            ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "contact_id" => $contacts->id,
            "name" => $contacts->name,
            "company" => $contacts->company,
            "street" => $contacts->street,
            "city" => $contacts->city,
            "zip" => $contacts->zip,
            "phone" => $contacts->phone,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->editDeliveryFormSucceeded;
        return $form;
    }

    function editDeliveryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
            ->where(array("id" => $form->values->contact_id))
            ->update(array(
                "company" => $form->values->company,
                "name" => $form->values->name,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "phone" => $form->values->phone,
            ));

        $this->redirect(":AdminStore:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Insert address request
     */
    function createComponentEditForm()
    {
        $order = $this->template->order;

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addSelect("shipping", "Poštovné", $this->template->shippingMethods)
            ->setAttribute("class", "form-control");
        $form->addSelect("payment", "Platební metoda", $this->template->paymentMethods)
            ->setAttribute("class", "form-control");
        $form->addTextArea("note_admin", "Moje poznámka")
            ->setAttribute("placeholder", "Moje poznámka")
            ->setAttribute("class", "form-control");
        $form->addText("bonustext", "Bonus")
            ->setAttribute("class", "form-control");
        $arr = array(
            "id" => $this->getParameter("id"),
            "shipping" => $order->store_settings_shipping_id,
            "payment" => $order->store_settings_payments_id,
            "bonustext" => $order->bonus,
            "note_admin" => $order->note_admin,
        );

        $form->setDefaults($arr);

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("orders")
            ->where(array("id" => $form->values->id))
            ->update(array(
                "note_admin" => $form->values->note_admin,
                "shipping_price" => $form->values->shipping,
                "payment_price" => $form->values->payment,
                "bonustext" => $this->template->order->bonus,
            ));

        $this->redirect(":AdminStore:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Change state
     */
    function createComponentChangeStateForm()
    {
        $form = $this->baseFormFactory->createUI();

        $orderStatesDb = $this->database->table("orders_states")->fetchPairs("id", "title");
        $orderStates = $orderStatesDb;

        $form->addHidden("id");
        $form->addHidden("email");
        $form->addSelect("state", "dictionary.main.Message", $orderStates)
            ->setAttribute("class", "form-control");

        $form->addCheckbox("identifier", " vytvořit identifikátor objednávky, pokud již nebyl vytvořen");
        $form->addCheckbox("send", " zaslat informaci e-mailem");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "state" => $this->template->order->orders_states_id,
            "email" => $this->template->order->email,
            "identifier" => 1,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->changeStateFormSucceeded;
        return $form;
    }

    function changeStateFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->identifier) {
            $order = new Model\Store\OrderIdentifier($this->database);
            $order->setId($form->values->id);
        }

        $this->database->table("orders")
            ->where(array("id" => $form->values->id))
            ->update(array(
                "orders_states_id" => $form->values->state,
                "bonus" => $form->values->bonustext,
            ));

        $templateLatte = substr(APP_DIR, 0, -4) . '/app/AdminStoreModule/templates/Settings/components/state-' . $form->values->state . '.latte';

        if ($form->values->send == "on" && file_exists($templateLatte)) {
            $orderId = $this->database->table("orders")->get($form->values->id);
            $latte = new \Latte\Engine;
            $params = array(
                'state' => $form->values->state,
                'oid' => $orderId->id,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site:title"] . ' <' . $this->template->settings["contacts:email:hq"] . '>')
                ->addTo($form->values->email)
                ->setHTMLBody($latte->renderToString($templateLatte, $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
        }

        $this->redirect(":AdminStore:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Change amount for order
     */
    function createComponentChangeAmountOrderForm()
    {
        $form = $this->baseFormFactory->createFF();
        $form->addHidden("id");
        $form->addHidden("order");
        $form->addText("amount")
            ->setType("number")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 60px;");

        $form->addSubmit("submitm", "dictionary.main.Change")
            ->setAttribute("class", "btn btn-cart-in btn-sm");

        $form->onSuccess[] = $this->changeAmountOrderFormSucceeded;
        return $form;
    }

    function changeAmountOrderFormSucceeded(\Nette\Forms\FilterForm $form)
    {
        if ($form->values->amount == 0) {
            $this->database->table("orders_items")
                ->get($form->values->id)
                ->delete();
        } else {
            $this->database->table("orders_items")
                ->get($form->values->id)->update(array(
                    "amount" => $form->values->amount,
                ));
        }

        $this->redirect(':AdminStore:Orders:detailItems', array("id" => $form->values->order));
    }

    /**
     * Delete item in order
     */
    function handleDeleteItem($id)
    {
        $this->database->table("orders_items")
            ->get($id)
            ->delete();

        $this->redirect(":Admin:Order:detailItems", array("id" => $this->getParameter('order')));
    }

    /**
     * Change state
     */
    function createComponentChangeStateQuickForm()
    {
        $form = $this->baseFormFactory->createFF();

        $orderStatesDb = $this->database->table("orders_states")->fetchPairs("id", "title");
        $orderStates = $orderStatesDb;

        $form->addHidden("id");
        $form->addHidden("email");
        $form->addSelect("state", "dictionary.main.Message", $orderStates)
            ->setAttribute("class", "form-control")
            ->setAttribute("style", "max-width: 100px; display: inline; margin: 0 10px;")
            ->setAttribute("placeholder", "messages.helpdesk.message");
        $form->addCheckbox("send", " zaslat mail");

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->changeStateQuickFormSucceeded;
        return $form;
    }

    function changeStateQuickFormSucceeded(\Nette\Forms\FilterForm $form)
    {
        $this->database->table("orders")
            ->where(array("id" => $form->values->id))
            ->update(array("orders_states_id" => $form->values->state));

        $templateLatte = substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/StoreSettings/components/state-' . $form->values->state . '.latte';

        if ($form->values->send == "on" && file_exists($templateLatte)) {
            $orderId = $this->database->table("orders")->get($form->values->id);
            $latte = new \Latte\Engine;
            $params = array(
                'state' => $form->values->state,
                'oid' => $orderId->id,
                'settings' => $this->template->settings,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site:title"] . ' <' . $this->template->settings["contacts:email:hq"] . '>')
                ->addTo($form->values->email)
                ->setHTMLBody($latte->renderToString($templateLatte, $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
        }

        $this->redirect(":AdminStore:Orders:default", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Delete order
     */
    function handleDelete($id)
    {
        $orderItems = $this->database->table("orders_items")->where("orders_id", $id);

        // Create order items
        foreach ($orderItems as $item) {
            /* Store Stock - update stocks */
            if ($this->template->settings['store:stock:deductStock']) {
                $this->database->table("stock")->where(array(
                    "store_id" => $item->store_id,
                    "id" => $item->stock_id,
                ))->update(array(
                    "amount" => new \Nette\Database\SqlLiteral("amount + " . $item->amount),
                    "amount_sold" => new \Nette\Database\SqlLiteral("amount_sold - " . $item->amount),
                ));
            } else {
                $this->database->table("stock")->where(array(
                    "pages_id" => $item->store_id,
                    "id" => $item->stock_id,
                ))->update(array(
                    "amount_sold" => new \Nette\Database\SqlLiteral("amount_sold - " . $item->amount),
                ));
            }
        }

        $this->database->table("orders")->get($id)->delete();

        $this->redirect(":AdminStore:Orders:default", array("id" => null));
    }

    function handleInvoice($id)
    {
        $residencyDb = $this->database->table("contacts")->get($this->template->settings['contacts:residency:contacts_id']);

        if ($residencyDb) {
            $residency = $residencyDb;
        } else {
            $residency = false;
        }

        $file = substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Homepage';
        $order = $this->database->table("orders")->get($id);

        $params = array(
            'order' => $order,
            'residency' => $residency,
            'settings' => $this->template->settings,
            'weights' => $this->database->table("store_settings_weights"),
        );

        $latte = new \Latte\Engine;
        $template = $latte->renderToString($file . "/components/invoice.latte", $params);
        $pdf = new \Joseki\Application\Responses\PdfResponse($template);
        $pdf->setSaveMode(\Joseki\Application\Responses\PdfResponse::INLINE);
        //$pdf->save(APP_DIR . '/files/invoices-125/', 'ivt-' . $order->oid . '.pdf');
        //echo APP_DIR . '/files/invoices-125' . '/' .  'ivt-' . $order->oid . '.pdf';
        //$pdf->setSaveMode(\Joseki\Application\Responses\PdfResponse::DOWNLOAD); //default behavior
        $this->sendResponse($pdf);
    }

    public function renderDefault()
    {
        $ordersDb = $this->database->table("orders")->where("orders_states_id NOT", null)->order("date_created DESC");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($ordersDb->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->orders = $ordersDb->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->args = $this->getParameters();
        $this->template->paginator = $paginator;
    }

}
