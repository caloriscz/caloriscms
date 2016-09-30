<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    protected function createComponentEditSettings()
    {
        $control = new \Caloriscz\Settings\EditSettingsControl($this->database);
        return $control;
    }

    /* Insert new language */
    function createComponentInsertLanguage()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addText("language", "Jazyk");
        $form->addText("code", "Kód");

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertLanguageSucceeded;
        $form->onValidate[] = $this->permissionValidated;
        return $form;
    }

    function permissionValidated(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $this->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->redirect(this);
        }
    }

    function insertLanguageSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $langExists = $this->database->table("languages")->where("title = ? OR code = ?",
            $form->values->language, $form->values->code);

        if ($langExists->count() > 0) {
            $this->flashMessage("Název jazyka nebo kód již existuje", "error");
            $this->redirect(":Admin:Settings:languages");
        } else {
            $this->database->table("languages")->insert(array(
                "title" => $form->values->language,
                "code" => $form->values->code,
            ));

            $this->redirect(":Admin:Settings:languages");
        }
    }

    /* Insert new country */
    function createComponentInsertCountry()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addText("country_cs", "Země (česky)");
        $form->addText("country_en", "Země (anglicky)");

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertCountrySucceeded;
        $form->onValidate[] = $this->permissionValidated;
        return $form;
    }

    function insertCountrySucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $exists = $this->database->table("countries")->where("title_cs = ? OR title_en = ?",
            $form->values->country_cs, $form->values->country_en);

        if ($exists->count() > 0) {
            $this->flashMessage("Země už je v seznamu", "error");
            $this->redirect(":Admin:Settings:countries");
        } else {
            $this->database->table("countries")->insert(array(
                "title_cs" => $form->values->country_cs,
                "title_en" => $form->values->country_en,
            ));

            $this->redirect(":Admin:Settings:countries");
        }
    }

    /* Insert new currency */
    function createComponentInsertCurrency()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addText("title", "dictionary.main.Title");
        $form->addText("symbol", "Symbol");
        $form->addText("code", "Köd");

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertCurrencySucceeded;
        $form->onValidate[] = $this->permissionValidated;
        return $form;
    }

    function insertCurrencySucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $exists = $this->database->table("countries")->where("title = ? OR code = ? OR symbol = ?",
            $form->values->country_cs, $form->values->country_en);

        if ($exists->count() > 0) {
            $this->flashMessage("Měna, symbol nebo kód už je v seznamu", "error");
            $this->redirect(":Admin:Settings:currency");
        } else {
            $this->database->table("countries")->insert(array(
                "title" => $form->values->title,
                "code" => $form->values->code,
                "symbol" => $form->values->symbol,
            ));

            $this->redirect(":Admin:Settings:countries");
        }
    }

    function handleInstall($id)
    {
        $default = $this->database->table("languages")->where("default = 1");

        if (strcmp($default->fetch()->code, $id) === "0") {
            $this->flashMessage("This is default language. Cannot be installed with suffix.");
            $this->redirect(":Admin:Settings:languages");
        }

        $this->checkColumn("pages", "title", "varchar(250)", $id);
        $this->checkColumn("pages", "slug", "varchar(250)", $id);
        $this->checkColumn("pages", "document", "text", $id);
        $this->checkColumn("pages", "preview", "varchar(250)", $id);
        $this->checkColumn("pages", "metakeys", "varchar(150)", $id);
        $this->checkColumn("pages", "metadesc", "varchar(200)", $id);
        $this->checkColumn("menu", "title", "varchar(80)", $id);
        $this->checkColumn("menu", "description", "text", $id);
        $this->checkColumn("snippets", "content", "text", $id);

        $this->redirect(":Admin:Settings:languages");
    }

    function handleMakeDefault($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $this->database->query("UPDATE languages SET `default` = NULL");
            $this->database->table("languages")->get($id)->update(array("default" => 1));
        }

        $this->redirect(":Admin:Settings:languages");
    }

    function handleMakeDefaultCurrency($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $this->database->query("UPDATE currencies SET `used` = NULL");
            $this->database->table("currencies")->get($id)->update(array("used" => 1));
        }

        $this->redirect(":Admin:Settings:languages");
    }

    function handleToggle($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $toggle = $this->database->table("languages")->get($id);

            if ($toggle->used) {
                $state = 0;
            } else {
                $state = 1;
            }

            $this->database->table("languages")->get($id)->update(array("used" => $state));
        }

        $this->redirect(":Admin:Settings:languages");
    }

    function handleToggleCountry($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $toggle = $this->database->table("countries")->get($id);

            if ($toggle->show) {
                $state = 0;
            } else {
                $state = 1;
            }

            $this->database->table("countries")->get($id)->update(array("show" => $state));
        }

        $this->redirect(":Admin:Settings:countries");
    }


    function checkColumn($table, $column, $type, $lang)
    {
        $pages_title = $this->database->query("SHOW COLUMNS FROM `" . $table . "` LIKE ?", $column . '_' . $lang)->getRowCount();

        if ($pages_title > 0) {
            $message = "shows: " . $column . " existed before";
        } else {
            $this->database->query("ALTER TABLE `" . $table . "` ADD `" . $column . "_" . $lang . "` " . $type);
            $message = "not shows" . $pages_title;
        }

        return $message . "<br>";
    }

    public function renderDefault()
    {
        $this->template->categoryId = $this->template->settings['categories:id:settings'];
    }

    public function renderLanguages()
    {
        $this->template->languages = $this->database->table("languages");
    }

    public function renderCountries()
    {
        $this->template->countries = $this->database->table("countries");
    }

    public function renderCurrencies()
    {
        $this->template->currencies = $this->database->table("currencies");
    }

}