<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class ContactsPresenter extends BasePresenter
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

        $this->template->contact = $this->database->table("contacts")
                ->get($this->getParameter("id"));
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addSubmit("submitm", "Create new contact")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $id = $this->database->table("contacts")
                ->insert(array(
            "contacts_groups_id" => 1,
        ));

        $this->redirect(":Admin:Contacts:detail", array("id" => $id));
    }

    /**
     * Delete contact
     */
    function handleDelete($id)
    {
        $this->database->table("contacts")->get($id)->delete();

        $this->redirect(":Admin:Contacts:default");
    }

    /**
     * Edit contact
     */
    function createComponentEditForm()
    {
        $groups = $this->database->table("contacts_groups")->order("group")->fetchPairs("id", "group");

        //echo $this->translator->getLocale();
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addGroup('');
        $form->addHidden('id');
        $form->addText("name", "Jméno")
                ->setAttribute("placeholder", "Jméno");
        $form->addText("post", "Pozice")
                ->setAttribute("placeholder", "Pozice")
                ->setOption("description", 1);
        $form->addText("email", "E-mail")
                ->setAttribute("placeholder", "E-mail")
                ->setAttribute("class", "form-control");
        $form->addText("phone", "Telefon")
                ->setAttribute("placeholder", "Telefon")
                ->setAttribute("class", "form-control");
        $form->addSelect("groups", "Skupina (kde se objeví)", $groups)
                ->setAttribute("class", "form-control");
        /*$form->addGroup('Address');
                 $form->addText("street", "Street")
          ->setAttribute("placeholder", "Ulice")
          ->setOption("description", 1);
          $form->addText("zip", "PSČ")
          ->setAttribute("placeholder", "ZIP")
          ->setOption("description", 1);
          $form->addText("city", "Město")
          ->setAttribute("placeholder", "Město")
          ->setOption("description", 1);
          $form->addGroup('FIremní údaje');
          $form->addText("urlweb", "Internetová adresa")
          ->setAttribute("placeholder", "Internetová adresa")
          ->setOption("description", 1);
          $form->addText("vatin", "IČ")
          ->setAttribute("placeholder", "IČ")
          ->setOption("description", 1);
          $form->addText("vatid", "DIČ")
          ->setAttribute("placeholder", "DIČ")
          ->setOption("description", 1); */
        $form->addGroup('Ostatní');
        $form->addTextArea("notes", "Specializace")
                ->setAttribute("class", "form-control");
                $form->addTextArea("user_odbornost", "Odbornost")
                ->setAttribute("class", "form-control");
        $form->addTextArea("cv", "CV")
                ->setAttribute("class", "form-control")
                ->setHtmlId("wysiwyg");

        $form->setDefaults(array(
            "name" => $this->template->contact->name,
            "post" => $this->template->contact->post,
            "email" => $this->template->contact->email,
            "phone" => $this->template->contact->phone,
            "groups" => $this->template->contact->contacts_groups_id,
//            "street" => $this->template->contact->street,
//            "zip" => $this->template->contact->zip,
//            "city" => $this->template->contact->city,
//            "urlweb" => $this->template->contact->url_web,
//            "vatin" => $this->template->contact->vatin,
//            "vatid" => $this->template->contact->vatid,
            "notes" => $this->template->contact->notes,
            "cv" => $this->template->contact->cv,
            "user_odbornost" => $this->template->contact->user_odbornost,
            "id" => $this->getParameter("id"),
        ));

        $form->addSubmit("submitm", "Uložit")
                ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE && strlen($form->values->email) > 0) {
            $this->flashMessage("Vyplňte pravý mail", "error");
            $this->redirect(":Admin:Contacts:default");
        }

        $this->database->table("contacts")
                ->where(array(
                    "id" => $form->values->id,
                ))
                ->update(array(
                    "name" => $form->values->name,
                    "post" => $form->values->post,
                    "email" => $form->values->email,
                    "phone" => $form->values->phone,
                    "contacts_groups_id" => $form->values->groups,
//                    "street" => $form->values->street,
//                    "zip" => $form->values->zip,
//                    "city" => $form->values->city,
//                    "url_web" => $form->values->url_web,
//                    "vatin" => $form->values->vatin,
//                   "vatid" => $form->values->vatid,
                    "notes" => $form->values->notes,
                    "cv" => $form->values->cv,
                    "user_odbornost" => $form->values->user_odbornost,
        ));

        $this->redirect(":Admin:Contacts:detail", array("id" => $form->values->id));
    }

    /**
     * Insert contact
     */
    function createComponentInsertGroupForm()
    {
        $form = new Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("group", "Název nové skupiny")
                ->setAttribute("placeholder", "Název skupiny")
                ->setAttribute("style", "width: 80%;");

        $form->addSubmit("submitm", "Vytvořit skupinu")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormGroupSucceeded;
        return $form;
    }

    /**
     * Insert group
     */
    function insertFormGroupSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts_groups")
                ->insert(array(
                    "group" => $form->values->group,
        ));

        $this->redirect(":Admin:Contacts:groups");
    }

    /**
     * Delete group
     */
    function handleDeleteGroup($id)
    {
        if ($id == 1) {
            $this->flashMessage("Hlavní skupinu nemůžete vymazat.", "error");
            $this->redirect(":Admin:Contacts:groups");
        }

        $this->database->table("contacts")->where(array("contacts_groups_id" => $id))->update(array("contacts_groups_id" => 1));

        $this->database->table("contacts_groups")->get($id)->delete();

        $this->redirect(":Admin:Contacts:groups");
    }

    /**
     * Insert hour
     */
    function createComponentInsertHourForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addSelect('day', 'Den v týdnu', array(1 => 'Pondělí', 2 => 'Úterý', 3 => 'Středa', 4 => 'Čtvrtek', 5 => 'Pátek', 6 => 'Sobota', 7 => 'Neděle'))
                ->setAttribute("class", "form-control");
        $form->addText('hourstext', 'Hodiny (např. 14.00-20.00) nebo zpráva (např. jen objednaní)');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->addSubmit("submitm", "Vytvořit")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertHourFormSucceeded;
        return $form;
    }

    function insertHourFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $contact = $this->database->table("contacts_openinghours")->where(array(
            "day" => $form->values->day,
            "contacts_id" => $form->values->id,
        ));

        if ($contact->count() > 0) {
            $this->database->table("contacts_openinghours")
                    ->where(array(
                        "contacts_id" => $form->values->id,
                        "day" => $form->values->day,
                    ))
                    ->update(array(
                        "hourstext" => $form->values->hourstext,
            ));
        } else {
            $this->database->table("contacts_openinghours")
                    ->insert(array(
                        "day" => $form->values->day,
                        "hourstext" => $form->values->hourstext,
                        "contacts_id" => $form->values->id,
            ));
        }


        $this->redirect(":Admin:Contacts:detail", array("id" => $form->values->id));
    }

    /**
     * Delete hour
     */
    function handleDeleteHour($id)
    {
        $this->database->table("contacts_openinghours")->get($id)->delete();

        $this->redirect(":Admin:Contacts:detail", array("id" => $this->getParameter("contact")));
    }

    public function renderDefault()
    {
        $this->template->contacts = $this->database->table("contacts")->order("name");
    }

    public function renderGroups()
    {
        $this->template->groups = $this->database->table("contacts_groups")->order("group");
    }

}
