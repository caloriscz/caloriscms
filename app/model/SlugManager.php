<?php

namespace Model;

use Kdyby\Translation\Translator;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;


class SlugManager
{

    /** @var Context */
    private $database;

    /** @var Translator @inject */
    public $translator;


    public function __construct(Context $database, Translator $translator)
    {
        $this->database = $database;
        $this->translator = $translator;
    }

    /**
     * @param $slug
     * @param null $lang
     * @param null $prefix
     * @return false|ActiveRow|null
     */
    public function getRowBySlug($slug, $lang = null, $prefix = null)
    {
        if (null !== $lang && $lang !== 'cs') {
            $arr['slug_'. $lang] = $slug;
        } else {
            $arr['slug'] = $slug;
        }

        if ($prefix !== null) {
            $arr['pages_types.prefix'] = $prefix;
        }

        $arr['public'] = 1;

        $db = $this->database->table('pages')->where($arr);
        $row = $db->fetch();

        return $db->count() > 0 ? $row : null;
    }

    /**
     * @return false|ActiveRow|null
     */
    public function getDefault()
    {
        $row = $this->database->table('pages')->where(['id' => 1])->fetch();
        return $row ? $row : null;
    }

    /**
     * @param $id
     * @return false|ActiveRow|null
     */
    public function getSlugById($id)
    {
        $row = $this->database->table('pages')->get($id);
        return $row ? $row : null;
    }

    /**
     * @return array
     */
    public function getLocale()
    {
        $languages = $this->translator->getAvailableLocales();

        foreach ($languages as $itemL) {
            $result = explode('_', $itemL);
            $iLocale[] = $result[0];
        }

        return $iLocale;
    }
}