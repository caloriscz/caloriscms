<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Database\Context;

/**
 * Image model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class File
{

    /** @var Context */
    public $database;
    public $user;
    private $path = "/media";
    private $pageId;
    private $file;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Set page identifier
     * @param $pageId
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param Set file name
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set type of the file
     * @param $type
     */
    public function setType($type)
        {
            $this->type = $type;
        }

    public function getType() {
        return $this->type;
    }

    public function create()
    {
        $this->database->table('media')->insert(array(
            'name' => $this->getFile(),
            'pages_id' => $this->getPageId(),
            'filesize' => filesize(APP_DIR . '/'. $this->path . '/' . $this->getPageId() . '/' . $this->getFile()),
            'file_type' => $this->getType(),
            'date_created' => date('Y-m-d H:i:s'),
        ));
    }

}