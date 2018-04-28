<?php
namespace Caloriscz\Events;

use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

/**
 * Image upload
 * Class ImageEditControl
 * @package Caloriscz\Events
 */
class ImageEditControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    function createComponentEditForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $imageTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

        $image = $this->database->table('media')->get($this->presenter->getParameter('name'));

        $form->addHidden('id');
        $form->addHidden('name');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'dictionary.main.Insert');

        $form->setDefaults([
            'id' => $this->presenter->getParameter('id'),
            'name' => $this->presenter->getParameter('name'),
            'description' => $image->description,
        ]);

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('media')->get($form->values->name)
            ->update([
                'description' => $form->values->description,
            ]);


        $this->presenter->redirect('this', [
            'id' => $form->values->id,
            'name' => $form->values->name,
        ]);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ImageEditControl.latte');

        $template->render();
    }

}