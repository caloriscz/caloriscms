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
     */
    public function handleDelete($id)
    {
        $imageDb = $this->database->table('pictures')->get($this->getParameter('name'));
        $imageDb->delete();

        IO::remove(APP_DIR . '/pictures/' . $id . '/' . $imageDb->name);
        IO::remove(APP_DIR . '/pictures/' . $id . '/tn/' . $imageDb->name);

        $this->redirect('this', ['id' => $this->presenter->getParameter('name')]);
    }

    /**
     * Set image as main  image
     */
    public function handleSetMain()
    {
        // Set all other media images in this folder as 0
        $this->database->table('pictures')->where(['pages_id' => $this->getParameter('id')])
            ->update(['main_file' => 0]);

        // Set chosen one as the main one
        $this->database->table('pictures')->get($this->getParameter('image'))->update(['main_file' => 1]);


        $this->presenter->redirect('this', ['id' => $this->getParameter('id'), 'image' => $this->getParameter('image')]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->catalogue = $this->database->table('pages')->get($this->presenter->getParameter('id'));
        $template->images = $this->database->table('pictures')
            ->where(['pages_id' => $this->presenter->getParameter('id')]);
        $template->setFile(__DIR__ . '/ImageBrowserControl.latte');
        $template->render();
    }
}
