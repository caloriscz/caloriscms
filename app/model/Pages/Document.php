<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Database\Context;
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
    private $public;
    private $title;
    private $pageTemplate;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function setTitle($title = false)
    {
        $this->title = $title;
        return $this->title;
    }

    public function getTitle()
    {
        if ($this->title) {
            return $this->title;
        } else {
            return false;
        }
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
        } else {
            return null;
        }
    }

    public function setMetaKey($metakey = false)
    {
        $this->metakey = $metakey;
        return $this->metakey;
    }

    public function getMetaKey()
    {
        if ($this->metakey) {
            return $this->metakey;
        } else {
            return false;
        }
    }

    public function setMetaDescription($metadesc = false)
    {
        $this->metadesc = $metadesc;
        return $this->metadesc;
    }

    public function getMetaDescription()
    {
        if ($this->metadesc) {
            return $this->metadesc;
        } else {
            return false;
        }
    }

    public function setSitemap($sitemap)
    {
        $this->sitemap = $sitemap;
    }

    public function getSitemap()
    {
        return $this->sitemap;
    }

    public function setDatePublished($date = false)
    {
        $this->date_published = $date;
        return $this->date_published;
    }

    public function getDatePublished()
    {
        if ($this->date_published) {
            return $this->date_published;
        } else {
            return false;
        }
    }

    public function setPublic($public = 0)
    {
        if ($public === 1) {
            $this->public = 1;
        } else {
            $this->public = 0;
        }

        return $this->public;
    }

    public function getPublic()
    {
        if (is_numeric($this->public)) {
            return $this->public;
        } else {
            return false;
        }
    }

    public function setLanguage($lang = false)
    {
        $this->lang = $lang;
        return $this->lang;
    }

    public function getLanguage()
    {
        if ($this->lang) {
            return $this->lang;
        } else {
            return false;
        }
    }

    public function createSlug($slug)
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
        } else {
            return false;
        }
    }

    public function setType($type = false)
    {
        $this->type = $type;
        return $this->type;
    }

    public function getType()
    {
        if ($this->type) {
            return $this->type;
        } else {
            return false;
        }
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
        } else {
            return false;
        }
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
        } else {
            return false;
        }
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
        $arr['users_id'] = $user;
        $arr['date_created'] = date('Y-m-d H:i:s');
        $arr['date_published'] = date('Y-m-d H:i:s');

        if ($category !== false) {
            $arr['pages_id'] = $category;
        }

        if ($this->getTitle()) {
            $arr['title'] = $this->getTitle();
        }

        $arr['pages_templates_id'] = $this->getTemplate();


        if ($this->getPreview()) {
            $arr['preview'] = $this->getPreview();
        }

        if ($this->getPublic()) {
            $arr['public'] = $this->getPublic();
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

        if ($this->getMetaKey()) {
            $arr['metakeys'] = $this->getMetaKey();
        }

        if ($this->getMetaDescription()) {
            $arr['metadesc'] = $this->getMetaDescription();
        }

        if ($this->getSitemap()) {
            $arr['sitemap'] = $this->getSitemap();
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
    public function save($id, $user = null)
    {
        if ($this->getTitle() && $this->getLanguage()) {
            $arr['title' . '_' . $this->getLanguage()] = $this->getTitle();
        } elseif ($this->getTitle()) {
            $arr['title'] = $this->getTitle();
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

        if ($this->getMetaKey() && $this->getLanguage()) {
            $arr['metakeys' . '_' . $this->getLanguage()] = $this->getMetaKey();
        } elseif ($this->getMetaKey()) {
            $arr['metakeys'] = $this->getMetaKey();
        }

        if ($this->getMetaDescription() && $this->getLanguage()) {
            $arr['metadesc' . '_' . $this->getLanguage()] = $this->getMetaDescription();
        } elseif ($this->getMetaDescription()) {
            $arr['metadesc'] = $this->getMetaDescription();
        }

        if ($this->getSitemap()) {
            $arr['sitemap'] = $this->getSitemap();
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

        if ($this->getDatePublished()) {
            $arr['date_published'] = $this->getDatePublished();
        } elseif ($this->getSlug()) {
            $arr['date_published'] = date('Y-m-d H:i:s');
        }

        if (is_numeric($this->getPublic())) {
            $arr['public'] = $this->getPublic();
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
    public function update($slug, $slugOld)
    {
        if ($this->checkReservedNames($slug)) {
            $slug .= '-name';
        }

        $slugNew = $this->generate($slug);
        $this->database->table('pages')->where('title', $slugOld)->update(array('title' => $slugNew));

        return $slugNew;
    }

    public function exists($slug)
    {
        $slugName = $this->database->table('pages')->where('title', $slug);

        if ($slugName->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove slug
     */
    public function remove($slugId)
    {
        if (is_numeric($slugId)) {
            $this->database->table('pages')->get($slugId)->delete();
        }
    }

    /**
     * Delete document
     */
    public function delete($id)
    {
        $this->database->table('pages')->get($id)->delete();

        return true;
    }

    /**
     * Names used by presenters
     * @param $slug
     * @return bool
     */
    public function checkReservedNames($slug)
    {
        $slugNames = [
            'blog', 'cart', 'catalogue', 'contacts', 'document', 'documents', 'error', 'events', 'gallery',
            'helpdesk', 'homepage', 'links', 'order', 'orders',
            'pricelist', 'product', 'profile', 'services', 'sign'
        ];

        return in_array($slug, $slugNames, true);
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

            while (in_array((++$max . '-' . $slug), $slugs)) ;

            return $max . '-' . $slug;
        } else {
            return $slug;
        }
    }

}