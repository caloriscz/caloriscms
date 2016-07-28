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
                    $paramArr["str"][] = ":store_params.param_id = ? AND :params.paramvalue = ?";
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
        $columns["pages.pages_types_id"] = 4;

        if ($this->category != FALSE) {
            $columns[":store_category.category_pages_id"] = $this->category;
        }

        if ($this->manufacturers != FALSE) {
            $columns["contacts.company LIKE ?"] = "%" . $this->manufacturers . "%";
            $columns["contacts.categories_id"] = $this->template->settings['categories:id:contactsBrands'];
        }

        if ($this->size != FALSE) {
            $columns["size LIKE ?"] = "%" . $this->size . "%";
        }

        if ($this->userf != FALSE) {
            $columns["users_id"] = $this->userf;
        }

        if ($this->price == TRUE) {
            $columns = array_merge((array)$columns, (array)$this->getPrice);
        }

        $connect = $this->database->table("pages")
            ->select(":store.id, pages.id, pages.slug, pages.title AS title, pages.slug AS slugtitle, pages.preview, 
            pages.date_created, pages.document, pages.date_published, :stock.amount, "
                . "SUM(:stock.amount) AS sumstock, price, "
                . ":params.param_id, :params.paramvalue, pages.sorted, pages.pages_types_id");

        $connect->where(':store_category.pages_id = pages.id'); // if not added, store_category may be referenced with category_pages_id
        $connect->group("pages.title, pages.document");

        if ($this->search != FALSE) {
            $connect->where("pages.title LIKE ? OR pages.document LIKE ?", "%" . $this->search . "%", "%" . $this->search . "%");
        }

        if (count($columns) > 0) {
            $connect->where($columns);
        }
/*
        if ($this->settings["store:stock:hideEmpty"] == 1) {
            $connect->having("SUM(stock.amount) > 0");
        }
*/
        $paramArr = $this->getParametres;
        if (count($paramArr["str"]) > 0) {
            for ($a = 0; $a < count($paramArr["str"]); $a++) {
                $connect->where($paramArr["str"][$a], $paramArr["key"][$a], $paramArr["value"][$a]);
                //echo $paramArr["str"][$a] .' - ' . $paramArr["key"][$a] .' - ' . $paramArr["value"][$a] . '<br />';
            }
        }

        if ($this->order == NULL) {
        } else {
            $connect->order($this->order);
        }

        return $connect;
    }

}
