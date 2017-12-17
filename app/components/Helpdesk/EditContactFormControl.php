<?php
namespace Caloriscz\Helpdesk;

use Nette\Application\UI\Control;
use Nette\Forms\BootstrapUIForm;

class EditContactFormControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit helpdesk
     */
    public function createComponentEditForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('helpdesk_id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'height: 200px;');
        $form->addCheckbox('fill_phone');

        $helpdesk = $this->database->table('helpdesk')->get($this->presenter->getParameter('id'));

        $form->setDefaults(array(
            'helpdesk_id' => $helpdesk->id,
            'title' => $helpdesk->title,
            'description' => $helpdesk->description,
            'fill_phone' => $helpdesk->fill_phone,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $this->database->table('helpdesk')->get($form->values->helpdesk_id)
            ->update(array(
                'title' => $form->values->title,
                'description' => $form->values->description,
                'fill_phone' => $form->values->fill_phone,
            ));

        $this->presenter->redirect(this, array('id' => $form->values->helpdesk_id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditContactFormControl.latte');

        $template->render();
    }

}