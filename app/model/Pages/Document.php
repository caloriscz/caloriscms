<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * Document model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Document
{

    /** @var Context */
    public $database;
    public $user;

    private $doc;
    private $preview;
    private $pageTemplate;
    private $date_published;
    private $slug;
    private $parent;
    private $lang;
    private $type;
    private $formValues;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function setForm($formValues): ArrayHash
    {
        $this->formValues = $formValues;
        return $this->formValues;
    }

    public function getForm()
    {
        if ($this->formValues) {
            return $this->formValues;
        }

        return false;
    }

    public function setType($type = false)
    {
        $this->type = $type;
        return $this->type;
    }

    public function getType(): int
    {
        if ($this->type) {
            return $this->type;
        }

        return false;
    }

    public function setTemplate($pageTemplate = false)
    {
        $this->pageTemplate = $pageTemplate;
        return $this->pageTemplate;
    }

    public function getTemplate()
    {
        if ($this->pageTemplate) {
            return $this->pageTemplate;
        }

        $pageType = $this->database->table('pages_types')->get($this->getType());

        if ($pageType) {
            return $pageType->pages_templates_id;
        }

        return null;
    }

    public function setLanguage($lang = false): bool
    {
        $this->lang = $lang;
        return $this->lang;
    }

    public function getLanguage()
    {
        if ($this->lang) {
            return $this->lang;
        }
    }

    public function createSlug(string $slug): string
    {
        $this->slug = $slug;

        return $this->slug;
    }

    public function setSlug($slugOld, $slug = false)
    {
        if ($slugOld !== $slug) {
            if ($this->database->table('pages')->where('slug', $slug)->count() > 0) {
                $this->slug = $this->generate($slugOld);
            } else {
                $this->slug = Strings::webalize($slug);
            }
        } else {
            $this->slug = false;
        }

        return $this->slug;
    }

    public function getSlug()
    {
        if ($this->slug) {
            return $this->slug;
        }

        return false;
    }

    public function setParent($parent = false)
    {
        if ($parent === false) {
            $this->parent = 0;
        } else {
            $this->parent = $parent;
        }

        return $this->parent;
    }

    public function getParent()
    {
        if ($this->parent) {
            return $this->parent;
        }

        return false;
    }

    public function setPreview($preview = false)
    {
        $this->preview = $preview;
        return $this->preview;
    }

    public function getPreview()
    {
        if ($this->preview) {
            return $this->preview;
        }

        return false;
    }

    public function setDocument($doc = false)
    {
        $this->doc = $doc;
        return $this->doc;
    }

    public function getDocument()
    {
        return $this->doc;
    }

    /**
     * Create new document
     * @param null $user
     * @param bool $category
     * @return bool|int|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow
     */
    public function create($user = null, $category = false)
    {
        $values = $this->getForm();

        $arr['users_id'] = $user;
        $arr['date_created'] = date('Y-m-d H:i:s');
        $arr['date_published'] = date('Y-m-d H:i:s');
        $arr['public'] = 0;
        $arr['pages_templates_id'] = $this->getTemplate();

        if ($category !== false) {
            $arr['pages_id'] = $category;
        }

        if ($values->title) {
            $arr['title'] = $values->title;
        }

        $arr['pages_templates_id'] = $this->getTemplate();

        if ($this->getPreview()) {
            $arr['preview'] = $this->getPreview();
        }

        if ($this->getParent()) {
            $arr['pages_id'] = $this->getParent();
        }

        if ($this->getSlug()) {
            $slug = $this->getSlug();

        } else {
            if ($this->checkReservedNames($arr['title'])) {
                $slug = $arr['title'] . '-name';
            } else {
                $slug = $arr['title'];
            }
        }

        $slugNew = $this->generate($slug);

        $arr['slug'] = $slugNew;
        $arr['pages_types_id'] = $this->getType();

        $id = $this->database->table('pages')->insert($arr);

        $this->database->query('SET @i = 1;UPDATE `pages` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC');

        return $id;
    }

    /**
     * Save document
     * @param $id
     * @param null $user
     * @return bool|int
     */
    public function save(int $id, $user = null): bool
    {
        $values = $this->getForm();
        $arr['sitemap'] = 0;

        if (isset($values->title) && $this->getLanguage()) {
            $arr['title' . '_' . $this->getLanguage()] = $values->title;
        } elseif (isset($values->title)) {
            $arr['title'] = $values->title;
        }

        $arr['pages_templates_id'] = $this->getTemplate();

        if ($this->getDocument() && $this->getLanguage()) {
            $arr['document' . '_' . $this->getLanguage()] = $this->getDocument();
        } else {
            $arr['document'] = $this->getDocument();
        }

        if ($this->getPreview() && $this->getLanguage()) {
            $arr['preview' . '_' . $this->getLanguage()] = $this->getPreview();
        } elseif ($this->getPreview()) {
            $arr['preview'] = $this->getPreview();
        }

        if (isset($values->metakeys) && $this->getLanguage()) {
            $arr['metakeys' . '_' . $this->getLanguage()] = $values->metakeys;
        } elseif (isset($values->metakeys)) {
            $arr['metakeys'] = $values->metakeys;
        }

        if (isset($values->metadesc) && $this->getLanguage()) {
            $arr['metadesc' . '_' . $this->getLanguage()] = $values->metadesc;
        } elseif (isset($values->metadesc)) {
            $arr['metadesc'] = $values->metadesc;
        }

        if (isset($values->sitemap)) {
            $arr['sitemap'] = 1;
        }

        if ($this->getSlug() && $this->getLanguage()) {
            $arr['slug' . '_' . $this->getLanguage()] = $this->getSlug();
        } elseif ($this->getSlug()) {
            $arr['slug'] = $this->getSlug();
        }

        if ($this->getParent()) {
            $arr['pages_id'] = $this->getParent();
        } elseif ($this->getParent() === 0) {
            $arr['pages_id'] = null;
        }

        if ($this->date_published) {
            $arr['date_published'] = $this->date_published;
        } elseif ($this->getSlug()) {
            $arr['date_published'] = date('Y-m-d H:i:s');
        }

        $arr['users_id'] = $user;

        return $this->database->table('pages')->get($id)->update($arr);
    }

    /**
     * Updates slug name
     * @param $slug
     * @param $slugOld
     * @return string
     */
    public function update(string $slug, string $slugOld): string
    {
        if ($this->checkReservedNames($slug)) {
            $slug .= '-name';
        }

        $slugNew = $this->generate($slug);
        $this->database->table('pages')->where('title', $slugOld)->update(array('title' => $slugNew));

        return $slugNew;
    }

    public function exists(string $slug): bool
    {
        $slugName = $this->database->table('pages')->where('title', $slug);
        return $slugName->count() > 0;
    }

    /**
     * Remove slug
     * @param $slugId
     * @return void
     */
    public function remove($slugId): void
    {
        if (is_numeric($slugId)) {
            $this->database->table('pages')->get($slugId)->delete();
        }
    }

    /**
     * Delete document
     * @param $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->database->table('pages')->get($id)->delete();

        return true;
    }

    /**
     * Names used by presenters
     * @param $slug
     * @return bool
     */
    public function checkReservedNames(string $slug): bool
    {
        $slugNames = [
            'blog', 'cart', 'catalogue', 'contacts', 'document', 'documents', 'error', 'events', 'gallery',
            'helpdesk', 'homepage', 'links', 'order', 'orders',
            'pricelist', 'product', 'profile', 'services', 'sign'
        ];

        return \in_array($slug, $slugNames, true);
    }

    /**
     * Generate slug
     * @param $slugToGenerate
     * @return null|string|string[]
     */
    public function generate($slugToGenerate)
    {
        $slug = Strings::webalize($slugToGenerate);

        $slugNameOne = $this->database->table('pages')->where('slug', $slug);

        if ($slugNameOne->count() === 0) {
            return $slug;
        }

        $max = 0;
        $slugName = $this->database->table('pages')->where('slug LIKE ?', '%' . $slug);

        if ($slugName->count() > 0) {

            $slugs = array_values($slugName->fetchPairs('slug', 'slug'));

            while (in_array((++$max . '-' . $slug), $slugs, true)) ;

            return $max . '-' . $slug;
        }

        return $slug;
    }

}