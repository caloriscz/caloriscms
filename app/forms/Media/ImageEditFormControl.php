<?php
namespace App\Forms\Media;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class ImageEditFormControl extends Control
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
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $image = $this->database->table('media')->get($this->getPresenter()->getParameter('image'));

        $form->addHidden('image_id');
        $form->addHidden('page_id');
        $form->addHidden('name');
        $form->addCheckbox('detail_view', ' Zobrazovat v galerii produktů');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'dictionary.main.Save');

        $form->setDefaults([
            'image_id' => $this->getPresenter()->getParameter('image'),
            'page_id' => $this->getPresenter()->getParameter('id'),
            'name' => $this->getPresenter()->getParameter('name'),
            'detail_view' => $image->detail_view,
            'description' => $image->description,
        ]);

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('media')->get($form->values->image_id)
            ->update([
                'description' => $form->values->description,
                'detail_view' => $form->values->detail_view,
            ]);

        $this->getPresenter()->redirect('this', [
            'id' => $form->values->page_id,
            'image' => $form->values->image_id,
        ]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ImageEditFormControl.latte');
        $this->template->render();
    }

}