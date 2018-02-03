<?php

namespace App\Forms\Profile;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class ChangePortraitControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    protected function createComponentChangePortraitForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addUpload('the_file', 'Vyberte obrázek (nepovinné)');
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = [$this, 'changePortraitFormSucceeded'];

        return $form;
    }

    public function changePortraitFormSucceeded()
    {
        $membersDb = $this->database->table('users')->where(['id' => $this->presenter->user->getId()]);

        if ($membersDb->count() > 0) {
            $uid = $membersDb->fetch()->id;

            if (file_exists(APP_DIR . '/images/profiles/portrait-' . $uid . '.jpg')) {
                IO::remove(APP_DIR . '/images/profiles/portrait-' . $uid . '.jpg');
                IO::upload(APP_DIR . '/images/profiles/', 'portrait-' . $uid . '.jpg');
            } else {
                IO::upload(APP_DIR . '/images/profiles/', 'portrait-' . $uid . '.jpg');
            }
        }

        $this->presenter->redirect(':Front:Profile:image');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ChangePortraitControl.latte');
        $template->render();
    }
}
