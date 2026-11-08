<?php
namespace App\Forms\Menu;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class MenuMenusEditControl extends Control
{
    /** menu_menus.title is varchar(30) and class varchar(60); strict MariaDB refuses longer values with a 500. */
    private const LENGTHS = ['title' => ['Název menu', 30], 'class' => ['Třída pro CSS styl', 60]];

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Edit Menu item form
     * @return BootstrapUIForm
     */
    protected function createComponentEditForm(): BootstrapUIForm
    {
        $menuMenus = $this->database->table('menu_menus')->get($this->presenter->getParameter('id'));
        if ($menuMenus === null) {
            $this->presenter->error('Record not found.', 404);
        }

        $form = new BootstrapUIForm();
        $form->addHidden('id');
        $form->addText('title');
        $form->addText('class');
        $form->addTextArea('description');
        $form->addSelect('type', 'Typ menu', ['Menu' => 'Menu', 'BadgesMenu' => 'BadgesMenu'])
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('submitm');

        $arr = [
            'id' => $menuMenus->id,
            'title' => $menuMenus->title,
            'class' => $menuMenus->class,
            'description' => $menuMenus->description,
            'type' => $menuMenus->type
        ];

        $form->setDefaults(array_filter($arr));
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function editFormSucceeded(BootstrapUIForm $form): void
    {
        // Read once: Nette warns when values are read from a form that has errors.
        $values = $form->getValues();
        $menuMenus = $this->database->table('menu_menus')->get($values->id);
        if ($menuMenus === null) {
            $this->presenter->error('Record not found.', 404);
        }

        foreach (self::LENGTHS as $field => [$label, $length]) {
            if (mb_strlen((string) $values->$field, 'UTF-8') > $length) {
                $form->addError(sprintf('%s může mít nejvýše %d znaků. Nic nebylo uloženo.', $label, $length));
            }
        }
        if ($form->hasErrors()) {
            return;
        }

        $menuMenus->update([
                'title' => $values->title,
                'description' => $values->description,
                'type' => $values->type,
                'class' => $values->class,
            ]);

        $this->presenter->redirect('this', ['id' => $values->id]);
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->languages = $this->database->table('languages')->where('default', null);
        $template->setFile(__DIR__ . '/MenuMenusEditControl.latte');
        $template->render();
    }

}
