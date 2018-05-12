<?php

namespace App\Forms\Lang;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditKeysControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('dictId');
        $form->addHidden('setkey');
        $form->addText('setvalue', 'dictionary.main.Description');

        $arr = array_filter(['dictId' => $this->presenter->getParameter('id')]);

        $form->setDefaults($arr);

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'editSucceeded'];
        $form->onValidate[] = [$this, 'permissionValidated'];
        return $form;
    }

    /**
     * @throws AbortException
     */
    public function permissionValidated(): void
    {
        if ($this->presenter->template->member->users_roles->settings === 0) {
            $this->presenter->flashMessage('Nemáte oprávnění k této akci', 'error');
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        foreach ($values['set'] as $key => $value) {
            $this->database->table('lang_keys')->get($key)
                ->update([
                    'path' => $value['path'],
                    'value_cs' => $value['value_cs'],
                    'value_en' => $value['value_en'],
                ]);
        }

        $this->presenter->redirect('this', ['id' => $form->values->dictId]);
    }

    /**
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('dictId');
        $form->addHidden('setkey');
        $form->addText('setvalue', 'dictionary.main.Description');

        $arr = array_filter(['dictId' => $this->presenter->getParameter('id')]);

        $form->setDefaults($arr);

        $form->addSubmit('send', 'dictionary.main.Insert')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'insertSucceeded'];
        return $form;
    }

    /**
     * @throws AbortException
     */
    public function insertSucceeded(BootstrapUIForm $form): void
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input
        $dictId = $form->values->dictId;

        foreach ($values['set'] as $key => $value) {
            $check = $this->database->table('lang_keys')->where([
                'lang_list_id' => $dictId,
                'directory' => $value['directory'],
                'path' => $value['path']
            ]);

            if (('' !== $value['directory'] || '' !== $value['path'] || '' !== $value['value_cs'] || '' !== $value['value_en']) || $check->count() === 0) {
                $this->database->table('lang_keys')->insert([
                    'lang_list_id' => $dictId,
                    'directory' => $value['directory'],
                    'path' => $value['path'],
                    'value_cs' => $value['value_cs'],
                    'value_en' => $value['value_en'],
                ]);
            }
        }

        $this->presenter->redirect('this', ['id' => $dictId]);
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id)
    {
        $this->database->table('lang_keys')->get($id)->delete();
        $this->presenter->redirect('this', ['id' => $this->presenter->getParameter('id')]);
    }

    public function render(): void
    {
        $dictId = $this->presenter->getParameter('id');

        $template = $this->getTemplate();
        $template->langSelected = $this->presenter->translator->getLocale();
        $template->database = $this->database;

        $template->keys = $this->database->table('lang_keys')->where(['lang_list_id' => $dictId])->order('directory, path');
        $template->listDirectories = $this->database->table('lang_keys')->select('directory, lang_list_id')->group('directory, lang_list_id')->having('lang_list_id = ?', $dictId);
        $template->setFile(__DIR__ . '/EditKeysControl.latte');

        $template->render();
    }

}