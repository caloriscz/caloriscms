<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class AdminBarControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Specifies type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->enabled = false;
        $template->page = false;

        $role = $this->presenter->user->getRoles();
        $template->roleCheck = $this->database->table('users_roles')->get($role[0]);

        if ($template->roleCheck && $template->settings['site:admin:adminBarEnabled'] && $this->presenter->template->member->adminbar_enabled) {
            $template->enabled = true;
        }

        if ($this->presenter->template->page) {
            $template->pageId = $this->database->table('pages')->get($this->presenter->template->page->id);
        } else {
            $template->pageId = null;
        }

        $template->presenterName = $this->presenter->getName();
        $template->presenterView = $this->presenter->getView();
        $template->slug = $this->presenter->getParameter('slug');
        $template->setFile(__DIR__ . '/AdminBarControl.latte');

        $template->render();
    }

}
