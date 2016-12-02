<?php

/*
 * Caloris Store
 * @copyright 2006-2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

/**
 * Get list of filtered products
 * @author Petr Karásek
 */
class Filter
{

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Fulltext
     */
    function setText()
    {
        if ($_GET["src"] == '') {
            $this->search = FALSE;
        } else {
            $this->search = $_GET["src"];
        }
    }

    /**
     * Options
     */
    function setOptions($options = FALSE)
    {
        if ($options == TRUE) {
            $this->settings = $options;
        }
    }

    /**
     * Order
     */
    function order($order)
    {
        switch ($order) {
            case 'pa':
                $order = "`price` ASC";
                break;
            case 'pd':
                $order = "`price` DESC";
                break;
            case 'na':
                $order = "`title` ASC";
                break;
            case 'nd':
                $order = "`title` DESC";
                break;
            case 'da':
                $order = "`date_published` ASC";
                break;
            case 'dd':
                $order = "`date_published` DESC";
                break;
            case 'sa':
                $order = "`stock`.`amount_sold` ASC";
                break;
            case 'sd':
                $order = "`stock`.`amount_sold` DESC";
                break;
            case 'oa':
                $order = "`sorted` ASC";
                break;
            case 'od':
                $order = "`sorted` DESC";
                break;
            default :
                $order = false;
        }

        $this->order = $order;
    }

    /**
     * Set manufacturer
     */
    function setManufacturer($listManufacturers)
    {
        if (strlen($listManufacturers) > 1) {
            $this->manufacturers = $listManufacturers;
        } else {
            $this->manufacturers = false;
        }
    }

    /**
     * Set size
     */
    function setSize($size)
    {
        if (strlen($size) > 0) {
            $this->size = $size;
        } else {
            $this->size = false;
        }
    }

    /**
     * Set categories
     */
    function setCategories($category)
    {
        if (is_array($category)) {
            $keys = array_keys($category);
            $this->category = implode(",", $keys);
        } elseif ($category == '') {
            $this->category = false;
        } else {
            $this->category = $category;
        }
    }

    /**
     * Set user
     */
    function setUser($user = '', $type = 0)
    {
        if (strlen($user) > 0) {

            if ($type == 0) {
                $this->userf = $user;
                $userDb = $this->database->table("users")->where(array("username" => $user));

                if ($userDb->count() > 0) {
                    $this->userf = $userDb->fetch()->id;
                } else {
                    $this->userf = false;
                }
            } else {
                $this->userf = $user;
            }
        } else {
            $this->userf = false;
        }
    }

    function stringize($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $val) {
                $arrNew[] = "'" . $val . "'";
            }
        }

        return $arrNew;
    }

    /**
     * Set price
     */
    function setPrice($priceFrom = null, $priceTo = null)
    {
        if ($priceFrom == '' && $priceTo == '') {
            $this->price = false;
        } elseif ($priceFrom == null && $priceTo !== null) {
            $this->price = true;
            $columns[":store.price <= ?"] = $priceTo;
        } elseif ($priceFrom !== null && $priceTo == null) {
            $this->price = true;
            $columns[":store.price >= ?"] = $priceFrom;
        } else {
            $this->price = true;
            $columns[":store.price >= ?"] = $priceFrom;
            $columns[":store.price <= ?"] = $priceTo;
        }

        $this->getPrice = $columns;

        return $this->getPrice;
    }

    /**
     * Add other columns
     * @param array $arr Array of columns with their values
     */
    function setColumns($arr)
    {
        $columns = null;

        foreach ($arr as $key => $value) {
            $columns[$key] = $value;
        }

        $this->getColumns = $columns;

        return $this->getColumns;
    }

    /**
     * Get parametres from check list of parametres
     */
    function setParametres($param)
    {
        if (count($param) > 0) {
            foreach ($param as $pmKey => $pmValue) {
                if (substr($pmKey, 0, 3) == 'pm_') {
                    $pmKeyPart = explode("_", $pmKey);

                    if ($pmKeyPart[2] == 'range') {
                        $rangeArr = explode("*", urldecode($pmValue));

                        $sqlKey = ":params.param_id = " . $pmKeyPart[1] . " AND :params.paramvalue BETWEEN ?";
                        $sqlValue = $rangeArr[0] . " AND " . $rangeArr[1];

                        unset($rangeArr);
                    } elseif ($pmKeyPart[2] == 'text') {
                        $rangeArr = explode("*", urldecode($pmValue));

                        $sqlKey = ":params.param_id = " . $pmKeyPart[1] . " AND :params.paramvalue = ?";
                        $sqlValue = "'" . $rangeArr[0] . "'";

                        unset($rangeArr);
                    } else {
                        $subParams = explode("*", urldecode($pmValue));

                        foreach ($subParams as $s) {
                            $paramsIn = "'" . $s . "', ";
                        }

                        $sqlKey = ":params.param_id = " . $pmKeyPart[1] . " AND :params.paramvalue IN (?)";
                        $sqlValue = substr($paramsIn, 0, -2);
                    }

                    $arr[$sqlKey] = new SqlLiteral($sqlValue);
                }
            }
        }

        if (count($arr) > 0) {
            $paramQuery = $this->connect->whereOr($arr)->having("COUNT(:params.pages_id) = ?", count($arr));
        }

        $this->getParametres = true;
        $this->paramQuery = $paramQuery;

        return $this->paramQuery;
    }

    /**
     * Get sql connection
     */
    function assemble()
    {
        $columns["pages.pages_types_id"] = 4;

        if ($this->category != false) {
            $columns[":store_category.category_pages_id"] = $this->category;
        } else {
        }

        if ($this->manufacturers != false) {
            $columns["contacts.company LIKE ?"] = "%" . $this->manufacturers . "%";
            $columns["contacts.categories_id"] = $this->template->settings['categories:id:contactsBrands'];
        }

        if ($this->size != false) {
            $columns["size LIKE ?"] = "%" . $this->size . "%";
        }

        if ($this->userf != false) {
            $columns["users_id"] = $this->userf;
        }

        if ($this->price == true) {
            $columns = array_merge((array)$columns, (array)$this->getPrice);
        }

        $this->connect->select(":store.id, pages.id, pages.slug, pages.title AS title, pages.slug AS slugtitle, pages.preview, 
            pages.date_created, pages.document, pages.date_published, pages.recommended, pages.public "
            /*. ", :stock.amount, SUM(:stock.amount) AS sumstock "*/
            . ", :store.price, :params.param_id, :params.paramvalue, "
            . ":store_prices.store_price_id, :store_prices.price AS storeprice, "
            . "pages.sorted, pages.pages_types_id");

        /* if not added, store_category may be referenced with category_pages_id; only when category is used */
        if ($this->category != false) {
            $this->connect->where(':store_category.pages_id = pages.id');
        }

        $this->connect->group("pages.title, pages.document");

        if ($this->search != false) {
            $this->connect->where("pages.title LIKE ? OR pages.document LIKE ?", "%" . $this->search . "%", "%" . $this->search . "%");
        }

        if (count($columns) > 0) {
            $this->connect->where((array)$this->getColumns);
        }

        if (count($columns) > 0) {
            $this->connect->where($columns);
        }

        /*
        if ($this->settings["store:stock:hideEmpty"] == 1) {
            $connect->having("SUM(stock.amount) > 0");
        }
        */

        $this->getParametres;

        if ($this->order == null) {
        } else {
            $this->connect->order($this->order);
        }

        return $this->connect;
    }

}