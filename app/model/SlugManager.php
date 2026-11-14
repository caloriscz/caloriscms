<?php

namespace Model;

use Symfony\Component\Translation\Translator;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;


class SlugManager
{

    private Explorer $database;

    /** @var Translator @inject */
    public $translator;


    public function __construct(Explorer $database, Translator $translator)
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
    public function getRowBySlug(string $slug, ?string $lang = null, ?string $prefix = null)
    {
        if (null !== $lang && $lang !== 'cs') {
            $arr['slug_'. $lang] = $slug;
        } else {
            $arr['slug'] = $slug;
        }

        if ($prefix !== null) {
            // Look the type up directly. A 'pages_types.prefix' condition joins through a database
            // foreign key from pages.pages_types_id, which older installations lack, and then every
            // two- or three-part URL (e.g. /pages/search/) failed before reaching its route.
            $typeIds = array_values($this->database->table('pages_types')->where('prefix', $prefix)
                ->fetchPairs(null, 'id'));
            if ($typeIds === []) {
                return null;
            }
            $arr['pages_types_id'] = $typeIds;
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
    public function getSlugById(int $id)
    {
        $row = $this->database->table('pages')->get($id);
        return $row ? $row : null;
    }

    public function getLocale(): array
    {
        $languages = $this->translator->getAvailableLocales();
        $iLocale  = [];

        foreach ($languages as $itemL) {
            $result = explode('_', $itemL);
            $iLocale[] = $result[0];
        }

        return $iLocale;
    }
}