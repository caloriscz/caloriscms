<?php

namespace App\FrontModule\Presenters;

/**
 * Links presenter.
 */
class LinksPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        $this->template->categories = $this->database->table('links_categories')
                ->where('parent_id', null)
                ->order('title');

        $catId = $this->getParameter('id');
        if ($catId) {
            $catByName = $this->database->table('categories')->where('slug', $catId);

            if ($catByName->count()) {
                $category = $catByName->fetch()->id;
            } else {
                $category = null;
            }
        } else {
            $category = null;
        }

        if ($this->getParameter('id')) {
            $this->template->links = $this->database->table('links')
                    ->where(['categories_id' => $category]);
        } else {
            $this->template->links = $this->database->table('links');
        }
        
        $this->template->youtube = "/^(http|https\:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/watch\?v\=[a-zA-Z0-9\-]+$/";
    }

}