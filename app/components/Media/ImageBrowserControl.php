<?php

namespace Caloriscz\Media;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class ImageBrowserControl extends Control
{

    /**
     * @var Context
     */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;

    }

    /**
     * Delete image
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $imageDb = $this->database->table('media')->get($this->getParameter('name'));
        $imageDb->delete();

        IO::remove(APP_DIR . '/media/' . $id . '/' . $imageDb->name);
        IO::remove(APP_DIR . '/media/' . $id . '/tn/' . $imageDb->name);

        $this->redirect('this', array('id' => $this->presenter->getParameter('name'),));
    }

    /**
     * Set image as main  image
     */
    public function handleSetMain()
    {
        // Set all other media images in this folder as 0
        $this->database->table("media")->where(array("pages_id" => $this->getParameter("id"), "file_type" => 1))
            ->update(array("main_file" => 0));

        // Set chosen one as the main one
        $this->database->table("media")->get($this->getParameter("image"))->update(array("main_file" => 1));


        $this->presenter->redirect(this, array("id" => $this->getParameter("id"), "image" => $this->getParameter("image")));
    }

    public function render()
    {
        $this->template->settings = $this->presenter->template->settings;
        $this->template->catalogue = $this->database->table("pages")->get($this->presenter->getParameter("id"));
        $this->template->images = $this->database->table("media")
            ->where(array("pages_id" => $this->presenter->getParameter("id"), "file_type" => 1));
        $this->template->setFile(__DIR__ . '/ImageBrowserControl.latte');

        $this->template->render();
    }

}
