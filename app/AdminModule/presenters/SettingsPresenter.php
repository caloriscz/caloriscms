<?php

namespace App\AdminModule\Presenters;

use App\Forms\Settings\InsertBlackListControl;
use App\Forms\Settings\EditSettingsControl;
use App\Forms\Settings\InsertCountryControl;
use App\Forms\Settings\InsertLanguageControl;
use Caloriscz\Settings\BlackListControl;
use Nette\Application\AbortException;

/**
 * Settings presenter.
 */
class SettingsPresenter extends BasePresenter
{
    private const LANGUAGE_COLUMNS = [
        'pages' => [
            'title' => 'varchar(250)',
            'slug' => 'varchar(250)',
            'document' => 'text',
            'preview' => 'varchar(250)',
            'metakeys' => 'varchar(150)',
            'metadesc' => 'varchar(200)',
        ],
        'menu' => [
            'title' => 'varchar(80)',
            'description' => 'text',
            'url' => 'text',
        ],
        'snippets' => [
            'content' => 'text',
        ],
    ];

    protected function createComponentEditSettings(): EditSettingsControl
    {
        return new EditSettingsControl($this->database);
    }

    protected function createComponentEditRssSettings(): EditSettingsControl
    {
        return new EditSettingsControl($this->database, 'rss:');
    }

    protected function createComponentInsertLanguage(): InsertLanguageControl
    {
        return new InsertLanguageControl($this->database);
    }

    protected function createComponentInsertCountry(): InsertCountryControl
    {
        return new InsertCountryControl($this->database);
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
        if (!$this->hasSettingsPermission()) {
            $this->denySettingsAction();
        }

        $language = $this->database->table('languages')->where('code', $id)->fetch();

        if (!$language || !preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', (string) $language->code)) {
            $this->flashMessage('Invalid language code.');
            $this->redirect('this');
        }

        $default = $this->database->table('languages')->where('default = 1')->fetch();

        if ($default && (string) $default->code === (string) $language->code) {
            $this->flashMessage('This is default language. Cannot be installed with suffix.');
            $this->redirect('this');
        }

        foreach (self::LANGUAGE_COLUMNS as $table => $columns) {
            foreach ($columns as $column => $type) {
                $this->checkColumn($table, $column, $type, (string) $language->code);
            }
        }

        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleMakeDefault($id): void
    {
        if (!$this->hasSettingsPermission()) {
            $this->denySettingsAction();
        }

        $language = $this->database->table('languages')->get($id);

        if ($language) {
            $this->database->query('UPDATE languages SET `default` = NULL');
            $language->update(['default' => 1]);
        }

        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleToggle($id): void
    {
        if (!$this->hasSettingsPermission()) {
            $this->denySettingsAction();
        }

        $toggle = $this->database->table('languages')->get($id);

        if ($toggle !== null) {
            $toggle->update(['used' => $toggle->used ? 0 : 1]);
        }

        $this->redirect(':Admin:Settings:languages');
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleToggleCountry($id): void
    {
        if (!$this->hasSettingsPermission()) {
            $this->denySettingsAction();
        }

        $toggle = $this->database->table('countries')->get($id);

        if ($toggle !== null) {
            $toggle->update(['show' => $toggle->show ? 0 : 1]);
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
        if (!isset(self::LANGUAGE_COLUMNS[$table][$column])
            || self::LANGUAGE_COLUMNS[$table][$column] !== $type
            || !preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', (string) $lang)
        ) {
            return '';
        }

        $columnName = $column . '_' . $lang;
        $pages_title = $this->database->query('SHOW COLUMNS FROM `' . $table . '` LIKE ?', $columnName)->getRowCount();

        if ($pages_title > 0) {
            $message = 'shows: ' . $column . ' existed before';
        } else {
            $this->database->query('ALTER TABLE `' . $table . '` ADD `' . $columnName . '` ' . $type);
            $message = 'not shows' . $pages_title;
        }

        return $message . '<br>';
    }

    private function hasSettingsPermission(): bool
    {
        return $this->template->memberRole && (int) $this->template->memberRole->settings === 1;
    }

    /**
     * @throws AbortException
     */
    private function denySettingsAction(): void
    {
        $this->flashMessage('Nemáte oprávnění k této akci', 'error');
        $this->redirect('this');
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

    public function renderRss(): void
    {
        $blogPageType = $this->database->table('pages_types')->get(2);
        $blogRootId = $blogPageType && $blogPageType->pages_id !== null ? (int) $blogPageType->pages_id : null;

        $parentIds = $this->database->table('pages')
            ->where('pages_types_id', 2)
            ->where('pages_id IS NOT NULL')
            ->fetchPairs('pages_id', 'pages_id');

        if ($blogRootId !== null) {
            unset($parentIds[$blogRootId]);
        }

        $this->template->rssGroups = [];

        if (count($parentIds) > 0) {
            $this->template->rssGroups = $this->database->table('pages')
                ->where('id IN ?', array_values($parentIds))
                ->order('title');
        }
    }
}
