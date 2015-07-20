<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class BlogPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Delete post
     */
    function handleDelete($id)
    {
        $this->database->table("blog")->get($id)->delete();
        $directory_images = APP_DIR . '/caloris_blog/' . $xmlSetting->directories->images;
        //IO::remove($directory_images . '/' . $cols["bid"] . '/header-' . $cols["bid"] . '.jpg');
        //IO::remove($directory_images . '/' . $cols["bid"]);

        $this->redirect(":Admin:Blog:default");
    }

    /**
     * Edit article
     */
    function createComponentEditForm()
    {
        $catalogue = $this->database->table("blog")
                ->get($this->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id', 'ID:');
        $form->addText('title', 'Název:');
        $form->addTextArea("article")
                ->setAttribute("class", "form-control")
                ->setHtmlId('wysiwyg');

        $form->addSubmit('submitm', 'Uložit');

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "title" => $catalogue->title,
            "article" => $catalogue->article,
        ));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    /**
     * Edit post
     */
    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("blog")->get($form->values->id)
                ->update(array(
                    "title" => $form->values->title,
                    "blog_categories_id" => 1,
                    "article" => $form->values->article,
                    "public" => 1,
        ));

        $this->redirect(":Admin:Blog:detail", array("id" => $form->values->id));
    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentInsertImageForm()
    {
        $catalogue = $this->database->table("blog")
                ->get($this->getParameter("id"));

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addUpload('the_file', 'Obrázek:');

        $form->addSubmit('submitm', 'Uložit');

        $form->setDefaults(array(
            "id" => $catalogue->id,
        ));

        $form->onSuccess[] = $this->insertImageFormSucceeded;
        return $form;
    }

    function insertImageFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {

        copy($_FILES["the_file"]["tmp_name"], APP_DIR . '/images/blog/' . $form->values->the_file->name);

        $image = \Nette\Utils\Image::fromFile(APP_DIR . '/images/blog/' . $form->values->the_file->name);
        $image->resize(250, 200, \Nette\Image::EXACT);
        $image->sharpen();
        $image->save(APP_DIR . '/images/blog/id-' . \Nette\Utils\Strings::padLeft($form->values->id, 6, '0') . '.jpg', 98, \Nette\Image::JPEG);
        chmod(APP_DIR . '/images/blog/id-' . \Nette\Utils\Strings::padLeft($form->values->id, 6, '0') . '.jpg', 0644);
        \App\Model\IO::remove(APP_DIR . '/images/blog/' . $form->values->the_file->name);
        header("Refresh: 0;url='REDIRECTION URI'");

        $this->redirect(":Admin:Blog:detail", array("id" => $form->values->id, "" => \Nette\Utils\Random::generate(4)));
    }

    function handleInsert()
    {
        $blogInsert = $this->database->table("blog")->insert(array(
            "title" => "Článek",
            "date_created" => date("Y-m-d H:i:s"),
            "public" => 0,
            "blog_categories_id" => 1,
        ));

        $this->redirect(":Admin:Blog:detail", array("id" => $blogInsert->id));
    }

    function handleDeleteImage($id)
    {
        \App\Model\IO::remove(APP_DIR . '/images/blog/id-' . \Nette\Utils\Strings::padLeft($id, 6, '0') . '.jpg');
        $this->redirect(":Admin:Blog:detail", array("id" => $id, "rnd" => \Nette\Utils\Random::generate(4)));
    }

    function handleChangeState($id, $public)
    {
        if ($public == 0) {
            $idState = 1;
        } else {
            $idState = 0;
        }

        $this->database->table("blog")->get($id)
                ->update(array(
                    "public" => $idState,
        ));

        $this->redirect(":Admin:Blog:default");
    }

    public function renderDefault()
    {
        $this->template->blog = $this->database->table("blog")->order("title");
    }

    public function renderDetail()
    {
        $this->template->blog = $this->database->table("blog")->where(array("id" => $this->getParameter("id")))->fetch();
    }

}
