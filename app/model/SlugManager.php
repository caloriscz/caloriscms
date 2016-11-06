<?php

namespace Model;

use Nette;


class SlugManager extends Nette\Object
{

    /** @var Nette\Database\Context */
    private $database;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;


    public function __construct(Nette\Database\Context $database, \Kdyby\Translation\Translator $translator)
    {
        $this->database = $database;
        $this->translator = $translator;
    }

    public function getRowBySlug($slug, $lang = null, $prefix = null)
    {
        if ($lang != null && $lang != 'cs') {
            $arr['slug_'. $lang] = $slug;
        } else {
            $arr['slug'] = $slug;
        }

        if ($prefix != null) {
            $arr['pages_types.prefix'] = $prefix;
        }

        $arr["public"] = 1;

        $db = $this->database->table("pages")->where($arr);
        $row = $db->fetch();

        if ($db->count() > 0) {
            return $row;
        } else {
            return NULL;
        }
    }

    public function getDefault()
    {
        $row = $this->database->table("pages")->where(array("id" => 1))->fetch();
        if ($row) {
            return $row;
        } else {
            return NULL;
        }
    }

    public function getSlugById($id)
    {
        $row = $this->database->table("pages")->get($id);
        if ($row) {
            return $row;
        } else {
            return NULL;
        }
    }

    public function getLocale()
    {
        $languages = $this->translator->getAvailableLocales();

        foreach ($languages as $itemL) {
            $result = explode("_", $itemL);
            $iLocale[] = $result[0];
        }

        return $iLocale;
    }
}