<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;

/**
 * Get list of filtered products
 * @property null getColumns
 * @author Petr Karásek
 */
class Filter
{

    /** @var Context */
    private $database;

    public $category;
    public $connect;
    public $columns;
    public $search;
    public $settings;
    public $order;

    public function __construct(Context $database)
    {
        $this->database = $database;
        $this->connect = $this->database->table('pages');
    }

    /**
     * Fulltext search, asearch string from get or post
     * @param string $searchString
     */
    public function setText(?string $searchString): void
    {
        $this->search = $searchString === '' ? false : $searchString;
    }

    /**
     * Options
     * @param bool $options
     */
    public function setOptions($options = false): void
    {
        if ($options === true) {
            $this->settings = $options;
        }
    }

    /**
     * Order
     * @param $order
     */
    public function setOrder($order): void
    {
        $arr = [
            'na' => '`title` ASC',
            'nd' => '`title` DESC',
            'da' => '`date_published` ASC',
            'dd' => '`date_published` DESC',
            'oa' => '`sorted` ASC',
            'od' => '`sorted` DESC'
        ];

        if (empty($order)) {
            $order = 'na';
        }

        $this->order = $arr[$order];
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set categories
     * @param $category
     */
    public function setCategories($category): void
    {
        if (\is_array($category)) {
            $keys = array_keys($category);
            $this->category = implode(',', $keys);
        } elseif ($category === '') {
            $this->category = false;
        } else {
            $this->category = $category;
        }
    }

    /**
     * @param $arr
     * @return array
     */
    public function stringize($arr): array
    {
        if (\is_array($arr)) {
            foreach ($arr as $val) {
                $arrNew[] = '\'' . $val . '\'';
            }
        }

        return $arrNew;
    }


    /**
     * Add other columns
     * @param $arr
     * @return mixed
     */
    public function setColumns($arr)
    {
        $columns = null;

        foreach ($arr as $key => $value) {
            $columns[$key] = $value;
        }

        $this->getColumns = $columns;

        $this->columns =  $this->getColumns;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get sql connection
     */
    public function assemble(): Selection
    {
        $this->connect->select('pages.id, pages.slug, pages.title AS title, pages.slug AS slugtitle, pages.preview, 
            pages.date_created, pages.document, pages.date_published, pages.recommended, pages.public, pages.pages_types_id');

        $this->connect->group('pages.title, pages.document');

        if ($this->search !== false) {
            $this->connect->where("pages.title LIKE ? OR pages.document LIKE ?", "%" . $this->search . "%", "%" . $this->search . "%");
        }

        if (\count($this->getColumns()) > 0) {
            $this->connect->where((array) $this->getColumns());
        }

        if (null === $this->getOrder()) {
        } else {
            $this->connect->order($this->getOrder());
        }

        return $this->connect;
    }

}