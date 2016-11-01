<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class ChangePortraitControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentChangePortraitForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addUpload("the_file", "Vyberte obrázek (nepovinné)");
        $form->addSubmit('submitm', 'dictionary.main.Insert');
        $form->onSuccess[] = $this->changePortraitFormSucceeded;

        return $form;
    }

    function changePortraitFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        // TODO peekay support for more types of images or image convertor

        $membersDb = $this->database->table("users")->where(array("id" => $this->presenter->user->getId()));

        if ($membersDb->count() > 0) {
            $uid = $membersDb->fetch()->id;

            if (file_exists(APP_DIR . '/images/profiles/portrait-' . $uid . '.jpg')) {
                \App\Model\IO::remove(APP_DIR . '/images/profiles/portrait-' . $uid . '.jpg');
                \App\Model\IO::upload(APP_DIR . '/images/profiles/', 'portrait-' . $uid . '.jpg');
            } else {
                \App\Model\IO::upload(APP_DIR . '/images/profiles/', 'portrait-' . $uid . '.jpg');
            }
        }

        $this->presenter->redirect(":Front:Profile:image");
    }

       public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ChangePortraitControl.latte');


        $template->render();
    }

}
