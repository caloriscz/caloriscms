<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Contacts presenter.
 */
class ContactsPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->contact = $this->database->table("contacts")
            ->where(array("pages_id" => $this->template->page->id))->fetch();
        $this->template->user = $this->database->table("users")->get($this->template->contact->users_id);
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm()
    {
        $form = $this->baseFormFactory->createPH();
        $form->addHidden("pages_id", $this->getParameter("id"));
        $form->addRadioList("type", "Osoba nebo organizace", array(0 => " osoby", 1 => " organizace"));
        $form->addText("title", "dictionary.main.Title")
            ->setRequired($this->translator->translate('messages.pages.NameThePage'));

        $form->setDefaults(array(
            "type" => 0
        ));

        $form->addSubmit("submitm", "dictionary.main.CreateNewContact")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $doc = new Model\Document($this->database);
        $doc->setType(5);
        $doc->createSlug("contact-" . $form->values->title);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->user->getId());
        Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

        if ($this->getParameter("id")) {
            $contactId = $this->getParameter("id");
        } else {
            $contactId = $this->template->settings['categories:id:contact'];
        }

        $arr = array(
            "users_id" => null,
            "pages_id" => $page,
            "type" => $form->values->type,
        );

        if ($form->values->type == 0) {
            $arr["name"] = $form->values->title;
        } else {
            $arr["company"] = $form->values->title;
        }

        $id = $this->database->table("contacts")
            ->insert($arr);

        $this->redirect(":Admin:Contacts:detail", array("id" => $page));
    }

    /**
     * Insert communication
     */
    function createComponentInsertCommunicationForm()
    {
        $types = array(
            "E-mail" => "E-mail", "Telefon, domácí" => "Telefon, domácí",
            "Telefon, pracovní" => "Telefon, pracovní", "Fax" => "Fax",
            "Webová adresa" => "Webová adresa", "Skype" => "Skype"
        );

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addSelect('type', 'Typ komunikace', $types)
            ->setAttribute('class', 'form-control');
        $form->addText('type_value', 'Hodnota');
        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->setDefaults(array(
            "id" => $this->getParameter('id'),
        ));

        $form->onSuccess[] = $this->insertCommunicationFormSucceeded;
        return $form;
    }

    function insertCommunicationFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $id = $this->database->table("contacts_communication")
            ->insert(array(
                "contacts_id" => $form->values->id,
                "communication_type" => $form->values->type,
                "communication_value" => $form->values->type_value,
            ));

        $this->redirect(":Admin:Contacts:detailCommunications", array("id" => $form->values->id));
    }

    /**
     * Delete contact
     */
    function handleDeleteCommunication($id)
    {
        $contacts = $this->database->table("contacts_communication")->get($id);
        $contactsId = $contacts->contacts_id;
        $contacts->delete();
        $this->redirect(":Admin:Contacts:detailCommunications", array("id" => $contactsId));
    }

    /**
     * Delete contact with all other tables and related page
     */
    function handleDelete($id)
    {
        for ($a = 0; $a < count($id); $a++) {
            $contacts = $this->database->table("contacts")->get($id[$a]);

            if ($contacts) {
                $page = $this->database->table("pages")->get($contacts->pages_id);

                if ($page) {
                    $doc = new Model\Document($this->database);
                    $doc->delete($page->id);
                    Model\IO::removeDirectory(APP_DIR . '/media/' . $page->id);

                    $this->database->table("contacts")->get($id[$a])->delete();
                }
            }
        }
        
        $this->redirect(":Admin:Contacts:default", array("id" => NULL));
    }

    /**
     * Edit contact
     */
    function createComponentEditForm()
    {
        $this->template->id = $this->getParameter('id');

        $categories = new Model\Category($this->database);
        $cats = $categories->getSubIds($this->template->settings['categories:id:contact']);
        $groups = $this->database->table("categories")
            ->where("id", $cats)->fetchPairs("id", "title");

        $form = $this->baseFormFactory->createUI();
        $form->addGroup('');
        $form->addHidden('contact_id');
        $form->addHidden('pages_id');
        $form->addText("name", "dictionary.main.Name")
            ->setAttribute("placeholder", "dictionary.main.Name");
        $form->addText("company", "dictionary.main.Company")
            ->setAttribute("placeholder", "dictionary.main.Company");
        $form->addRadioList("type", "Osoba nebo organizace", array(0 => " osoby", 1 => " organizace"));
        $form->addText("post", "dictionary.main.Post")
            ->setAttribute("placeholder", "dictionary.main.Post")
            ->setOption("description", 1);
        $form->addText("email", "E-mail")
            ->setAttribute("placeholder", "E-mail")
            ->setAttribute("class", "form-control");
        $form->addText("phone", "dictionary.main.Phone")
            ->setAttribute("placeholder", "dictionary.main.Phone")
            ->setAttribute("class", "form-control");
        $form->addSelect("categories_id", "dictionary.main.Category", $groups)
            ->setAttribute("class", "form-control");

        $form->addGroup('Adresa');
        $form->addText("street", "Ulice")
            ->setAttribute("placeholder", "Ulice")
            ->setOption("description", 1);
        $form->addText("zip", "PSČ")
            ->setAttribute("placeholder", "PSČ")
            ->setOption("description", 1);
        $form->addText("city", "Město")
            ->setAttribute("placeholder", "Město")
            ->setOption("description", 1);
        $form->addGroup('Firemní údaje');
        $form->addText("vatin", "IČ")
            ->setAttribute("placeholder", "dictionary.main.VatIn")
            ->setOption("description", 1);
        $form->addSubmit("loadVatIn", "Načíst")->onClick[] = $this->loadVatInFormSucceeded;
        $form->addText("vatid", "DIČ")
            ->setAttribute("placeholder", "dictionary.main.VatId")
            ->setHtmlId("kurzy_ico")
            ->setOption("description", 1);
        $form->addText("banking_account", "Bankovní účet")
            ->setAttribute("placeholder", "Bankovní účet")
            ->setOption("description", 1);
        $form->addGroup('Ostatní');
        $form->addTextArea("notes", "dictionary.main.Notes")
            ->setAttribute("class", "form-control");


        $form->setDefaults(array(
            "contact_id" => $this->template->contact->id,
            "pages_id" => $this->template->contact->pages_id,
            "name" => $this->template->contact->name,
            "company" => $this->template->contact->company,
            "post" => $this->template->contact->post,
            "type" => $this->template->contact->type,
            "email" => $this->template->contact->email,
            "phone" => $this->template->contact->phone,
            "categories_id" => $this->template->contact->categories_id,
            "street" => $this->template->contact->street,
            "zip" => $this->template->contact->zip,
            "city" => $this->template->contact->city,
            "banking_account" => $this->template->contact->banking_account,
            "vatin" => $this->template->contact->vatin,
            "vatid" => $this->template->contact->vatid,
            "notes" => $this->template->contact->notes,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->editFormSucceeded;
        $form->onValidate[] = $this->editFormValidated;
        return $form;
    }

    function editFormValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if (\Nette\Utils\Validators::isEmail($form->values->email) == FALSE && strlen($form->values->email) > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.fillInEmail'), "error");
            $this->redirect(":Admin:Contacts:detail", array("id" => NULL));
        }
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
            ->where(array(
                "id" => $form->values->contact_id,
            ))
            ->update(array(
                "name" => $form->values->name,
                "company" => $form->values->company,
                "post" => $form->values->post,
                "type" => $form->values->type,
                "email" => $form->values->email,
                "phone" => $form->values->phone,
                "categories_id" => $form->values->categories_id,
                "street" => $form->values->street,
                "zip" => $form->values->zip,
                "city" => $form->values->city,
                "vatin" => $form->values->vatin,
                "vatid" => $form->values->vatid,
                "banking_account" => $form->values->banking_account,
                "notes" => $form->values->notes,
            ));

        $this->redirect(":Admin:Contacts:detail", array("id" => $form->values->pages_id));
    }

    function loadVatInFormSucceeded(Nette\Forms\Controls\SubmitButton $button)
    {
        if ($button->form->values->vatin) {
            $ares = new \h4kuna\Ares\Ares();
            $aresArr = $ares->loadData(str_replace(" ", "", $button->form->values->vatin))->toArray();

            if (count($aresArr) > 0) {
                $this->database->table("contacts")
                    ->where(array(
                        "id" => $button->form->values->contact_id,
                    ))
                    ->update(array(
                        "name" => $aresArr['company'],
                        "street" => $aresArr['street'],
                        "zip" => $aresArr['zip'],
                        "city" => $aresArr['city'],
                        "vatin" => $aresArr['in'],
                        "vatid" => $aresArr['tin'],
                    ));
            } else {
                $this->flashMessage($this->translator->translate('messages.sign.NotFound'), "error");
            }
        } else {
            $this->flashMessage($this->translator->translate('messages.sign.NotFound'), "error");
        }

        $this->redirect(":Admin:Contacts:detail", array("id" => $button->form->values->pages_id));
    }

    /**
     * Insert hour
     */
    function createComponentInsertHourForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addSelect('day', 'dictionary.main.DayOfTheWeek', array(
            1 => 'dictionary.days.Monday', 2 => 'dictionary.days.Tuesday', 3 => 'dictionary.days.Wednesday',
            4 => 'dictionary.days.Thursday', 5 => 'dictionary.days.Friday', 6 => 'dictionary.days.Saturday', 7 => 'dictionary.days.Sunday'))
            ->setAttribute("class", "form-control");
        $form->addText('hourstext', 'Hodiny (např. 14.00-20.00) nebo zpráva (např. jen objednaní)')
            ->setRequired('Vložte hodiny od-do nebo nějakou informaci');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
        ));

        $form->addSubmit("submitm", "dictionary.main.Insert")
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertHourFormSucceeded;
        $form->onValidate[] = $this->validateHourFormSucceeded;
        return $form;
    }

    function validateHourFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if (strlen($form->values->hourstext) < 1) {
            $this->flashMessage('Vložte hodiny od-do nebo nějakou informaci', 'error');
            $this->redirect(':Admin:Contacts:detailOpeningHours', array("id" => $form->values->id));
        }
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


        $this->redirect(":Admin:Contacts:detailOpeningHours", array("id" => $form->values->id));
    }

    /**
     * Delete hour
     */
    function handleDeleteHour($id)
    {
        $this->database->table("contacts_openinghours")->get($id)->delete();

        $this->redirect(":Admin:Contacts:detailOpeningHours", array("id" => $this->getParameter("contact")));
    }

    public function createComponentCommunicationsGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        $dbCommunications = $this->database->table("contacts_communication")
            ->where(array("contacts_id" => $this->getParameter('id')));

        $grid->setDataSource($dbCommunications);
        $grid->setItemsPerPageList(array(20));

        $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDeleteCommunication'];

        $grid->addColumnText('communication_type', 'Typ');
        $grid->addColumnText('communication_value', 'Hodnota');

        $grid->setTranslator($this->translator);
    }

    protected function createComponentContactsGrid($name)
    {

        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);

        if ($this->id == NULL) {
            $contacts = $this->database->table("contacts");
        } else {
            $contacts = $this->database->table("contacts")->where("categories_id", $this->id);
        }

        $grid->setDataSource($contacts);
        $grid->addGroupAction($this->translator->translate('dictionary.main.Delete'))->onSelect[] = [$this, 'handleDelete'];


        $grid->addColumnLink('name', 'Název')
            ->setRenderer(function ($item) {
                if (strlen($item->name) == 0 && strlen($item->company) == 0) {
                    $name = 'nemá název';
                } elseif (strlen($item->name) == 0) {
                    $name = $item->company;
                } else {
                    $name = $item->name;
                }

                $url = Nette\Utils\Html::el('a')->href($this->link('detail', array("id" => $item->pages_id)))
                    ->setText($name);
                return $url;
            })
            ->setSortable();
        $grid->addFilterText('name', $this->translator->translate('dictionary.main.Name'));
        $grid->addColumnText('email', $this->translator->translate('dictionary.main.Email'))
            ->setSortable();
        $grid->addFilterText('email', $this->translator->translate('dictionary.main.Email'));
        $grid->addColumnText('phone', $this->translator->translate('dictionary.main.Phone'))
            ->setSortable();
        $grid->addFilterText('phone', $this->translator->translate('dictionary.main.Phone'));
        $grid->addColumnText('vatin', $this->translator->translate('dictionary.main.VatIn'))
            ->setSortable();
        $grid->addFilterText('vatin', 'dictionary.main.VatIn');
        $grid->addColumnText('street', $this->translator->translate('dictionary.main.Address'))
            ->setRenderer(function ($item) {
                $address = $item->street . ', ' . $item->zip . ' ' . $item->city;
                if (strlen($address) > 2) {
                    $addressText = $address;
                } else {
                    $addressText = NULL;
                }
                return $addressText;
            })
            ->setSortable();
        $grid->addFilterText('street', 'dictionary.main.Street');

        $grid->setTranslator($this->translator);
    }

    /**
     * Image Upload
     */
    function createComponentUploadForm()
    {
        $form = $this->baseFormFactory->createUI();
        $imageTypes = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

        $form->addHidden("idc");
        $form->addHidden("tag");
        $form->addUpload('the_file', 'dictionary.main.InsertImage')
            ->addRule(\Nette\Forms\Form::MIME_TYPE, 'Invalid type of image:', $imageTypes);
        $form->addTextarea("description", "dictionary.main.Description")
            ->setAttribute("class", "form-control");
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults(array(
            "idc" => $this->getParameter("id"),
            "tag" => 'portrait',
        ));

        $form->onSuccess[] = $this->uploadFormSucceeded;
        return $form;
    }

    function uploadFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $thumbName = 'tn_';
        $album = \Nette\Utils\Strings::padLeft($form->values->idc, 6, '0');
        $fileDirectory = APP_DIR . '/images/contacts/contact/' . $album;
        Model\IO::directoryMake($fileDirectory, 0755);

        if (strlen($_FILES["the_file"]["tmp_name"]) > 1) {
            $imageExists = $this->database->table("contacts_images")->where(array(
                'filename' => $_FILES["the_file"]["name"],
                'contacts_id' => $form->values->idc,
            ));

            $fileNameBase = $_FILES["the_file"]["name"];

            $fileName = $fileDirectory . '/' . $fileNameBase;
            \App\Model\IO::remove($fileName);

            copy($_FILES["the_file"]["tmp_name"], $fileName);
            chmod($fileName, 0644);

            if ($imageExists->count() == 0) {
                $this->database->table("contacts_images")->insert(array(
                    'filename' => $fileNameBase,
                    'contacts_id' => $form->values->idc,
                    'description' => $form->values->description,
                    'tag' => $form->values->tag,
                    'filesize' => filesize($fileDirectory . '/' . $_FILES["the_file"]["name"]),
                    'date_created' => date("Y-m-d H:i:s"),
                ));
            }

            // thumbnails
            $image = \Nette\Utils\Image::fromFile($fileName);
            $image->resize(400, 250, \Nette\Utils\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(APP_DIR . '/images/contacts/contact/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"]);
            chmod(APP_DIR . '/images/contacts/contact/' . $album . '/' . $thumbName . $_FILES["the_file"]["name"], 0777);
        }

        $this->redirect(":Admin:Contacts:detailImages", array(
            "id" => $form->values->idc,
            "category" => $form->values->category,
        ));
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:contact'];
        $contactsDb = $this->database->table("contacts")->order("name");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($contactsDb->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->args = $this->getParameters();
        $this->template->paginator = $paginator;
        $this->template->contacts = $contactsDb->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->menu = $this->database->table("categories")->where('parent_id', $this->template->settings['categories:id:contact']);
    }

public function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderImages()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderMember()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

    public function renderCommunications()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
        $this->template->communications = $this->database->table("contacts_communications")->where(array(
            "contacts_id" => $this->getParameter('id'),
        ));
    }

}
