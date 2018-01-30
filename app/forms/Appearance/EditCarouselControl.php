<?php

namespace Caloriscz\Appearance;

use App\Model\IO;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class EditCarouselControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Edit category
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm()
    {
        $carousel = $this->database->table('carousel')->get($this->getPresenter()->getParameter('id'));

        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('carousel_id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addTextArea('description', 'dictionary.main.Description')
            ->setAttribute('class', 'form-control')
            ->setAttribute('style', 'max-height: 150px;');
        $form->addText('uri', 'dictionary.main.URL');
        $form->addCheckbox('visible', 'dictionary.main.Show');
        $form->addUpload('the_file', 'dictionary.main.Icon');
        $form->addSubmit('submitm', 'dictionary.main.Save');


        $arr = array(
            'carousel_id' => $carousel->id,
            'title' => $carousel->title,
            'description' => $carousel->description,
            'visible' => $carousel->visible,
            'uri' => $carousel->uri,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(BootstrapUIForm $form)
    {
        $image = $form->values->the_file->name;

        $arr = array(
            'title' => $form->values->title,
            'description' => $form->values->description,
            'uri' => $form->values->uri,
            'visible' => $form->values->visible,
        );

        if ($form->values->the_file->error === 0) {
            $arr['image'] = $image;

            if (file_exists(APP_DIR . '/images/carousel/' . $image)) {

                IO::remove(APP_DIR . '/images/carousel/' . $image);
                IO::upload(APP_DIR . '/images/carousel/', $image);
            } else {
                IO::upload(APP_DIR . '/images/carousel/', $image);
            }
        }

        $this->database->table('carousel')->get($form->values->carousel_id)->update($arr);


        $this->redirect('this', array('carousel_id' => $form->values->carousel_id));
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/EditCarouselControl.latte');
        $this->template->render();
    }

}
