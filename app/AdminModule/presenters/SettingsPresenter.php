<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Menus\Admin\AdminPanelControl;
use Caloriscz\Menus\Admin\SettingsCategoriesControl;

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

    protected function createComponentInsertLanguage()
    {
        $control = new \Caloriscz\Settings\Languages\InsertLanguageControl($this->database);
        return $control;
    }

    protected function createComponentInsertCountry()
    {
        $control = new \Caloriscz\Settings\Countries\InsertCountryControl($this->database);
        return $control;
    }

    protected function createComponentInsertCurrency()
    {
        $control = new \Caloriscz\Settings\Currency\InsertCurrencyControl($this->database);
        return $control;
    }

    protected function createComponentSettingsCategories()
    {
        $control = new SettingsCategoriesControl($this->database);
        return $control;
    }
    
    public function handleInstall($id)
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

    public function handleMakeDefault($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $this->database->query("UPDATE languages SET `default` = NULL");
            $this->database->table("languages")->get($id)->update(array("default" => 1));
        }

        $this->redirect(":Admin:Settings:languages");
    }

    public function handleMakeDefaultCurrency($id)
    {
        if ($this->template->member->users_roles->settings_edit == 0) {
            $this->database->query("UPDATE currencies SET `used` = NULL");
            $this->database->table("currencies")->get($id)->update(array("used" => 1));
        }

        $this->redirect(":Admin:Settings:languages");
    }

    public function handleToggle($id)
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

    public function handleToggleCountry($id)
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


    public function checkColumn($table, $column, $type, $lang)
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