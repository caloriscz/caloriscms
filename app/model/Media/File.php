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
    private $path = '/media';
    private $pageId;
    private $file;
    private $type;

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
     * Set file name
     * @param $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
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

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    public function create(): void
    {
        $this->database->table('media')->insert([
            'name' => $this->getFile(),
            'pages_id' => $this->getPageId(),
            'filesize' => filesize(APP_DIR . '/'. $this->path . '/' . $this->getPageId() . '/' . $this->getFile()),
            'file_type' => $this->getType(),
            'date_created' => date('Y-m-d H:i:s'),
        ]);
    }

}