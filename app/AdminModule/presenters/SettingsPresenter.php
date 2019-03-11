<?php

namespace App\AdminModule\Presenters;

use App\Forms\Settings\InsertBlackListControl;
use App\Forms\Settings\EditSettingsControl;
use App\Forms\Settings\InsertCountryControl;
use App\Forms\Settings\InsertCurrencyControl;
use App\Forms\Settings\InsertLanguageControl;
use Caloriscz\Settings\BlackListControl;
use Nette\Application\AbortException;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    protected function createComponentEditSettings(): EditSettingsControl
    {
        return new EditSettingsControl($this->database);
    }

    protected function createComponentInsertLanguage(): InsertLanguageControl
    {
        return new InsertLanguageControl($this->database);
    }

    protected function createComponentInsertCountry(): InsertCountryControl
    {
        return new InsertCountryControl($this->database);
    }

    protected function createComponentInsertCurrency(): InsertCurrencyControl
    {
        return new InsertCurrencyControl($this->database);
    }

    protected function createComponentInsertBlackList(): InsertBlackListControl
    {
        return new InsertBlackListControl($this->database);
    }

    protected function createComponentBlackList(): BlackListControl
    {
        return new BlackListControl($this->database);
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleInstall($id): void
    {
        $default = $this->database->table('languages')->where('default = 1');

        if (strcmp($default->fetch()->code, $id) === '0') {
            $this->flashMessage('This is default language. Cannot be installed with suffix.');
            $this->redirect('this');
        }

        $this->checkColumn('pages', 'title', 'varchar(250)', $id);
        $this->checkColumn('pages', 'slug', 'varchar(250)', $id);
        $this->checkColumn('pages', 'document', 'text', $id);
        $this->checkColumn('pages', 'preview', 'varchar(250)', $id);
        $this->checkColumn('pages', 'metakeys', 'varchar(150)', $id);
        $this->checkColumn('pages', 'metadesc', 'varchar(200)', $id);
        $this->checkColumn('menu', 'title', 'varchar(80)', $id);
        $this->checkColumn('menu', 'description', 'text', $id);
        $this->checkColumn('menu', 'url', 'text', $id);
        $this->checkColumn('snippets', 'content', 'text', $id);

        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleMakeDefault($id): void
    {
        if ($this->template->member->users_roles->settings === 0) {
            $this->database->query('UPDATE languages SET `default` = NULL');
            $this->database->table('languages')->get($id)->update(['default' => 1]);
        }

        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleMakeDefaultCurrency($id): void
    {
        if ($this->template->member->users_roles->settings === 0) {
            $this->database->query('UPDATE currencies SET `used` = NULL');
            $this->database->table('currencies')->get($id)->update(['used' => 1]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleToggle($id): void
    {
        if ($this->template->member->users_roles->settings === 0) {
            $toggle = $this->database->table('languages')->get($id);

            $state = $toggle->used ? 0 : 1;

            $this->database->table('languages')->get($id)->update(['used' => $state]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleToggleCountry($id): void
    {
        if ($this->template->member->users_roles->settings === 0) {
            $toggle = $this->database->table('countries')->get($id);

            $state = $toggle->show ? 0 : 1;

            $this->database->table('countries')->get($id)->update(['show' => $state]);
        }

        $this->redirect(':Admin:Settings:countries');
    }

    /**
     * @param $table
     * @param $column
     * @param $type
     * @param $lang
     * @return string
     */
    public function checkColumn($table, $column, $type, $lang): string
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

    public function renderGlobal(): void
    {
        $this->template->categoryId = $this->getParameter('id');
    }

    public function renderLanguages(): void
    {
        $this->template->languages = $this->database->table('languages');
    }

    public function renderCountries(): void
    {
        $this->template->countries = $this->database->table('countries');
    }

    public function renderCurrencies(): void
    {
        $this->template->currencies = $this->database->table('currencies');
    }

    public function renderPageTypes(): void
    {
        $this->template->pagesTypes = $this->database->table('pages_types');
    }

    public function renderPageTemplates(): void
    {
        $this->template->pagesTemplates = $this->database->table('pages_templates');
    }

    public function renderUserRoles(): void
    {
        $this->template->usersRoles = $this->database->table('users_roles');
    }
}