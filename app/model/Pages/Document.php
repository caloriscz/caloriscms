<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Utils\Strings;

/**
 * Cart model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Document
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function setTitle($title = false)
    {
        $this->title = $title;
        return $this->title;
    }

    function getTitle()
    {
        if ($this->title) {
            return $this->title;
        } else {
            return false;
        }
    }

    function setMetaKey($metakey = false)
    {
        $this->metakey = $metakey;
        return $this->metakey;
    }

    function getMetaKey()
    {
        if ($this->metakey) {
            return $this->metakey;
        } else {
            return false;
        }
    }

    function setMetaDescription($metadesc = false)
    {
        $this->metadesc = $metadesc;
        return $this->metadesc;
    }

    function getMetaDescription()
    {
        if ($this->metadesc) {
            return $this->metadesc;
        } else {
            return false;
        }
    }

    function setDatePublished($date = false)
    {
        $this->date_published = $date;
        return $this->date_published;
    }

    function getDatePublished()
    {
        if ($this->date_published) {
            return $this->date_published;
        } else {
            return false;
        }
    }

    function setPublic($public = 0)
    {
        if ($public == 1) {
            $this->public = 1;
        } else {
            $this->public = 0;
        }

        return $this->public;
    }

    function getPublic()
    {
        if (is_numeric($this->public)) {
            return $this->public;
        } else {
            return false;
        }
    }

    function setLanguage($lang = false)
    {
        $this->lang = $lang;
        return $this->lang;
    }

    function getLanguage()
    {
        if ($this->lang) {
            return $this->lang;
        } else {
            return false;
        }
    }

    function createSlug($slug)
    {
        $this->slug = $slug;

        return $this->slug;
    }

    function setSlug($slugOld, $slug = false)
    {
        if ($slugOld !== $slug) {
            if ($this->database->table("pages")->where("slug", $slug)->count() > 0) {
                $this->slug = $this->generate($slugOld);
            } else {
                $this->slug = Strings::webalize($slug);
            }
        } else {
            $this->slug = false;
        }

        return $this->slug;
    }

    function getSlug()
    {
        if ($this->slug) {
            return $this->slug;
        } else {
            return false;
        }
    }

    function setType($type = false)
    {
        $this->type = $type;
        return $this->type;
    }

    function getType()
    {
        if ($this->type) {
            return $this->type;
        } else {
            return false;
        }
    }

    function setParent($parent = false)
    {
        if ($parent == false) {
            $this->parent = 0;
        } else {
            $this->parent = $parent;
        }

        return $this->parent;
    }

    function getParent()
    {
        if ($this->parent) {
            return $this->parent;
        } else {
            return false;
        }
    }

    function setPreview($preview = false)
    {
        $this->preview = $preview;
        return $this->preview;
    }

    function getPreview()
    {
        if ($this->preview) {
            return $this->preview;
        } else {
            return false;
        }
    }


    function setDocument($doc = false)
    {
        $this->doc = $doc;
        return $this->doc;
    }

    function getDocument()
    {
        if ($this->doc) {
            return $this->doc;
        } else {
            return false;
        }
    }

    /**
     * Create new document
     */
    function create($user = null, $category = false)
    {
        $arr["users_id"] = $user;
        $arr["date_created"] = date("Y-m-d H:i:s");
        $arr["date_published"] = date("Y-m-d H:i:s");

        if ($category != false) {
            $arr["pages_id"] = $category;
        }

        if ($this->getTitle()) {
            $arr["title"] = $this->getTitle();
        }

        if ($this->getPreview()) {
            $arr["preview"] = $this->getPreview();
        }

        if ($this->getMetaKey()) {
            $arr["metakeys"] = $this->getMetaKey();
        }

        if ($this->getPublic()) {
            $arr["public"] = $this->getPublic();
        }

        if ($this->getParent()) {
            $arr["pages_id"] = $this->getParent();
        }

        if ($this->getSlug()) {
            $slug = $this->getSlug();

        } else {
            if ($this->checkReservedNames($arr["title"])) {
                $slug = $arr["title"] . '-name';
            } else {
                $slug = $arr["title"];
            }
        }

        $slugNew = $this->generate($slug);

        $arr["slug"] = $slugNew;
        $arr["pages_types_id"] = $this->getType();

        $id = $this->database->table("pages")
            ->insert($arr);

        $this->database->query("SET @i = 1;UPDATE `pages` SET `sorted` = @i:=@i+2 ORDER BY `sorted` ASC");

        return $id;


    }

    /**
     * Save document
     */
    function save($id, $user = null)
    {
        if ($this->getTitle() && $this->getLanguage()) {
            $arr["title" . '_' . $this->getLanguage()] = $this->getTitle();
        } elseif ($this->getTitle()) {
            $arr["title"] = $this->getTitle();
        }

        if ($this->getDocument() && $this->getLanguage()) {
            $arr["document" . '_' . $this->getLanguage()] = $this->getDocument();
        } elseif ($this->getDocument()) {
            $arr["document"] = $this->getDocument();
        }

        if ($this->getPreview() && $this->getLanguage()) {
            $arr["preview" . '_' . $this->getLanguage()] = $this->getPreview();
        } elseif ($this->getPreview()) {
            $arr["preview"] = $this->getPreview();
        }

        if ($this->getMetaKey() && $this->getLanguage()) {
            $arr["metakeys" . '_' . $this->getLanguage()] = $this->getMetaKey();
        } elseif ($this->getMetaKey()) {
            $arr["metakeys"] = $this->getMetaKey();
        }

        if ($this->getMetaDescription() && $this->getLanguage()) {
            $arr["metadesc" . '_' . $this->getLanguage()] = $this->getMetaDescription();
        } elseif ($this->getMetaDescription()) {
            $arr["metadesc"] = $this->getMetaDescription();
        }

        if ($this->getSlug() && $this->getLanguage()) {
            $arr["slug" . '_' . $this->getLanguage()] = $this->getSlug();
        } elseif ($this->getSlug()) {
            $arr["slug"] = $this->getSlug();
        }

        if ($this->getParent()) {
            $arr["pages_id"] = $this->getParent();
        } elseif ($this->getParent() == 0) {
            $arr["pages_id"] = null;
        }

        if ($this->getDatePublished()) {
            $arr["date_published"] = $this->getDatePublished();
        } elseif ($this->getSlug()) {
            $arr["date_published"] = date("Y-m-d H:i:s");
        }

        if (is_numeric($this->getPublic())) {
            $arr["public"] = $this->getPublic();
        }

        $arr["users_id"] = $user;

        $page = $this->database->table("pages")->get($id)
            ->update($arr);

        return $page;
    }

    /**
     * Updates slug name
     * @param $slug
     * @param $slugOld
     * @return string
     */
    function update($slug, $slugOld)
    {
        if ($this->checkReservedNames($slug)) {
            $slug = $slug . '-name';
        }

        $slugNew = $this->generate($slug);
        $this->database->table("pages")->where("title", $slugOld)->update(array("title" => $slugNew));

        return $slugNew;
    }

    function exists($slug)
    {
        $slugName = $this->database->table("pages")->where("title", $slug);

        if ($slugName->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove slug
     */
    function remove($slugId)
    {
        if (is_numeric($slugId)) {
            $this->database->table("pages")->get($slugId)->delete();
        }
    }

    /**
     * Delete document
     */
    function delete($id)
    {
        $this->database->table("pages")->get($id)->delete();

        return true;
    }

    /**
     * Names used by presenters
     */
    function checkReservedNames($slug)
    {
        $slugNames = array(
            "blog", "cart", "catalogue", "contacts", "document", "documents", "error", "events", "gallery",
            "helpdesk", "homepage", "links", "order", "orders",
            "pricelist", "product", "profile", "services", "sign"
        );

        return in_array($slug, $slugNames);
    }

    /**
     * Generate slug
     */
    function generate($slugToGenerate)
    {
        $slug = Strings::webalize($slugToGenerate);

        $slugNameOne = $this->database->table("pages")->where("slug", $slug);

        if ($slugNameOne->count() == 0) {
            return $slug;
        }

        $max = 0;
        $slugName = $this->database->table("pages")->where("slug LIKE ?", '%' . $slug);

        if ($slugName->count() > 0) {

            $slugs = array_values($slugName->fetchPairs("slug", "slug"));

            while (in_array((++$max . '-' . $slug), $slugs)) ;

            return $max . '-' . $slug;
        } else {
            return $slug;
        }
    }

}