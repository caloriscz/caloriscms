<?php

namespace App\AdminModule\Presenters;

use App\Forms\Settings\EditSettingsControl;
use App\Forms\Settings\InsertCountryControl;
use App\Forms\Settings\InsertCurrencyControl;
use App\Forms\Settings\InsertLanguageControl;
use Caloriscz\Settings\SettingsCategoriesControl;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    protected function createComponentEditSettings()
    {
        return new EditSettingsControl($this->database);
    }

    protected function createComponentInsertLanguage()
    {
        return new InsertLanguageControl($this->database);
    }

    protected function createComponentInsertCountry()
    {
        return new InsertCountryControl($this->database);
    }

    protected function createComponentInsertCurrency()
    {
        return new InsertCurrencyControl($this->database);
    }

    protected function createComponentSettingsCategories()
    {
        return new SettingsCategoriesControl($this->database);
    }

    public function handleInstall($id)
    {
        $default = $this->database->table('languages')->where('default = 1');

        if (strcmp($default->fetch()->code, $id) === '0') {
            $this->flashMessage('This is default language. Cannot be installed with suffix.');
            $this->redirect(':Admin:Settings:languages');
        }

        $this->checkColumn('pages', 'title', 'varchar(250)', $id);
        $this->checkColumn('pages', 'slug', 'varchar(250)', $id);
        $this->checkColumn('pages', 'document', 'text', $id);
        $this->checkColumn('pages', 'preview', 'varchar(250)', $id);
        $this->checkColumn('pages', 'metakeys', 'varchar(150)', $id);
        $this->checkColumn('pages', 'metadesc', 'varchar(200)', $id);
        $this->checkColumn('pages_categories', 'title', 'varchar(100)', $id);
        $this->checkColumn('menu', 'title', 'varchar(80)', $id);
        $this->checkColumn('menu', 'description', 'text', $id);
        $this->checkColumn('menu', 'url', 'text', $id);
        $this->checkColumn('snippets', 'content', 'text', $id);

        $this->redirect(':Admin:Settings:languages');
    }

    public function handleMakeDefault($id)
    {
        if ($this->template->member->users_roles->settings === 0) {
            $this->database->query('UPDATE languages SET `default` = NULL');
            $this->database->table('languages')->get($id)->update(['default' => 1]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    public function handleMakeDefaultCurrency($id)
    {
        if ($this->template->member->users_roles->settings === 0) {
            $this->database->query('UPDATE currencies SET `used` = NULL');
            $this->database->table('currencies')->get($id)->update(['used' => 1]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    public function handleToggle($id)
    {
        if ($this->template->member->users_roles->settings === 0) {
            $toggle = $this->database->table('languages')->get($id);

            if ($toggle->used) {
                $state = 0;
            } else {
                $state = 1;
            }

            $this->database->table('languages')->get($id)->update(['used' => $state]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    public function handleToggleCountry($id)
    {
        if ($this->template->member->users_roles->settings == 0) {
            $toggle = $this->database->table('countries')->get($id);

            if ($toggle->show) {
                $state = 0;
            } else {
                $state = 1;
            }

            $this->database->table('countries')->get($id)->update(['show' => $state]);
        }

        $this->redirect(':Admin:Settings:countries');
    }


    public function checkColumn($table, $column, $type, $lang)
    {
        $pages_title = $this->database->query('SHOW COLUMNS FROM `' . $table . '` LIKE ?', $column . '_' . $lang)->getRowCount();

        if ($pages_title > 0) {
            $message = 'shows: ' . $column . ' existed before';
        } else {
            $this->database->query('ALTER TABLE `' . $table . '` ADD `' . $column . '_' . $lang . '` ' . $type);
            $message = 'not shows' . $pages_title;
        }

        return $message . '<br>';
    }

    public function renderDefault()
    {
        $this->template->categoryId = null;
    }

    public function renderLanguages()
    {
        $this->template->languages = $this->database->table('languages');
    }

    public function renderCountries()
    {
        $this->template->countries = $this->database->table('countries');
    }

    public function renderCurrencies()
    {
        $this->template->currencies = $this->database->table('currencies');
    }

    public function renderPageTypes()
    {
        $this->template->pagesTypes = $this->database->table('pages_types');
    }

    public function renderPageTemplates()
    {
        $this->template->pagesTemplates = $this->database->table('pages_templates');
    }

    public function renderUserRoles()
    {
        $this->template->usersRoles = $this->database->table('users_roles');
    }
}