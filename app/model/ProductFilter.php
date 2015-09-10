<?php

/*
 * Caloris Store
 * @copyright 2006-2013 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Get list of filtered products
 * @author Petr Karásek
 */
class ProductFilter
{

    /** @var Nette\Database\Context */
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
     * Order
     */
    function order($order)
    {
        switch ($order) {
            case 'pa':
                $order = "`pricefinal` ASC";
                break;
            case 'pd':
                $order = "`pricefinal` DESC";
                break;
            case 'na':
                $order = "`title` ASC";
                break;
            case 'nd':
                $order = "`title` DESC";
                break;
            case 'da':
                $order = "`date_modified` ASC";
                break;
            case 'dd':
                $order = "`date_modified` DESC";
                break;
            default :
                $order = "`date_modified` DESC";
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
            $this->manufacturers = FALSE;
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
            $this->size = FALSE;
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
            $this->category = FALSE;
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
                    $this->userf = FALSE;
                }
            } else {
                $this->userf = $user;
            }
        } else {
            $this->userf = FALSE;
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
    function setPrice($priceFrom = NULL, $priceTo = NULL)
    {
        if ($priceFrom == '' && $priceTo == '') {
            $this->price = FALSE;
        } elseif ($priceFrom == NULL && $priceTo !== NULL) {
            $this->price = TRUE;
            $columns["pricefinal <= ?"] = $priceTo;
        } elseif ($priceFrom !== NULL && $priceTo == NULL) {
            $this->price = TRUE;
            $columns["pricefinal >= ?"] = $priceFrom;
        } else {
            $this->price = TRUE;
            $columns["pricefinal >= ?"] = $priceFrom;
            $columns["pricefinal <= ?"] = $priceTo;
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
        $columns = NULL;

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
        $params = $param["param"];

        if (count($params) > 0) {
            foreach ($params as $key => $values) {
                foreach ($values as $value) {
                    // WHERE store_params.store_params_id = KEY and store_params.paramvalue = VALUE AND
                    $paramArr["str"][] = ":store_params.store_param_id = ? AND :store_params.paramvalue = ?";
                    $paramArr["key"][] = $key;
                    $paramArr["value"][] = $value;
                    // how to create array if it couldn't be repeated                
                    //$paramArr[""] = $value;
                }
            }
        }

        $this->getParametres = $paramArr;

        return $this->getParametres;
    }

    /**
     * Get sql connection
     */
    function assemble()
    {
        if ($this->category != FALSE) {
            $columns[":store_category.store_categories_id"] = $this->category;
        }

        if ($this->manufacturers != FALSE) {
            $columns["store_brands.title LIKE ?"] = "%" . $this->manufacturers . "%";
        }

        if ($this->size != FALSE) {
            $columns["size LIKE ?"] = "%" . $this->size . "%";
        }

        if ($this->userf != FALSE) {
            $columns["users_id"] = $this->userf;
        }

        if ($this->price == TRUE) {
            $columns = array_merge((array) $columns, (array) $this->getPrice);
        }

        $connect = $this->database->table("store")
                ->select("store.id, store.title, store.description, date_modified, :store_stock.amount, "
                . "SUM(:store_stock.amount) AS sumstock, MIN(:store_stock.price) AS minprice, "
                . ":store_params.store_param_id, :store_params.paramvalue");

        $connect->group("store.title, description");

        if ($this->search != FALSE) {
            $connect->where("store.title LIKE ? OR store.description LIKE ?", "%" . $this->search . "%", "%" . $this->search . "%");
        }

        if (count($columns) > 0) {
            $connect->where($columns);
        }

        $connect->having("SUM(:store_stock.amount) > 0");

        $paramArr = $this->getParametres;
        if (count($paramArr["str"]) > 0) {
            for ($a = 0; $a < count($paramArr["str"]);$a++) {
                $connect->where($paramArr["str"][$a], $paramArr["key"][$a], $paramArr["value"][$a]);
                //echo $paramArr["str"][$a] .' - ' . $paramArr["key"][$a] .' - ' . $paramArr["value"][$a] . '<br />';
            }
        }


        $connect->order($this->order);

        return $connect;
    }

}
