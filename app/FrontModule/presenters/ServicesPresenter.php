<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Services presenter.
 */
class ServicesPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $page = $this->database->table("pages")->get($this->getParameter("page_id"));

        if ($this->translator->getLocale() == $this->translator->getDefaultLocale()) {
            $this->template->title = $page->title;
            $this->template->body = $page->document;
            $this->template->metakeys = $page->metakeys;
            $this->template->metadesc = $page->metadesc;
        } else {
            $this->template->title = $page->{'title_' . $this->translator->getLocale()};
            $this->template->body = $page->{'document_' . $this->translator->getLocale()};
            $this->template->metadesc = $page->{'metakeys_' . $this->translator->getLocale()};
            $this->template->metakeys = $page->{'metadesc_' . $this->translator->getLocale()};

        }


    }

}
