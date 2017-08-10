<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Utils\Strings;

/**
 * Image model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class File
{

    /** @var \Nette\Database\Context */
    public $database;
    public $user;
    private $path = "/media";

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param Set page id
     */
    function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param Set file name
     */
    function setFile($file)
    {
        $this->file = $file;
    }

    function getFile()
    {
        return $this->file;
    }

    /**
     * @param Set type: image = 1, file = 0
     */
    function setType($type)
        {
            $this->type = $type;
        }

    function getType() {
        return $this->type;
    }

    function create()
    {
        $this->database->table("media")->insert(array(
            'name' => $this->getFile(),
            'pages_id' => $this->getPageId(),
            'filesize' => filesize(APP_DIR . "/". $this->path . "/" . $this->getPageId() . "/" . $this->getFile()),
            'file_type' => $this->getType(),
            'date_created' => date("Y-m-d H:i:s"),
        ));
    }

}