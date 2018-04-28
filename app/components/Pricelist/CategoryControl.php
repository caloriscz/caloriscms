<?php

namespace Caloriscz\Pricelist;

use Joseki\Application\Responses\PdfResponse;
use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;

class CategoryControl extends Control
{

    /** @var Context */
    public $database;

    /**
     * CategoryControl constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Generate PDF
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleGeneratePdf($id): void
    {
        $file = substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/Pricelist';

        $pricelist = $this->database->table('pricelist')
            ->select('pricelist.id, pricelist.pricelist_categories_id, pricelist.title AS amenu, pricelist.sorted, pricelist.price, pricelist_categories.title')
            ->order('pricelist_categories_id, sorted DESC');

        $params = [
            'pricelist' => $pricelist,
            'settings' => $this->presenter->template->settings,
        ];

        $latte = new Engine();
        $template = $latte->renderToString($file . '/pricelist.latte', $params);
        $pdf = new PdfResponse($template);

        $pdf->documentTitle = 'Ceník';

        $pdf->setSaveMode(PdfResponse::INLINE);
        $pdf->save(APP_DIR . '/files/pdf/', 'ivt-' . '.pdf');
        $pdf->setSaveMode(PdfResponse::DOWNLOAD); //default behavior
        $this->presenter->sendResponse($pdf);
    }

    /**
     * Insert category
     * @return BootstrapUIForm
     */
    protected function createComponentInsertCategoryForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('parent_id');
        $form->addHidden('type');
        $form->addText('title', 'dictionary.main.title')
            ->setAttribute('class', 'form-control');
        $form->addSubmit('submitm', 'dictionary.main.insert')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'insertCategoryFormSucceeded'];
        $form->onValidate[] = [$this, 'validateCategoryFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function validateCategoryFormSucceeded(BootstrapUIForm $form): void
    {
        $redirectTo = $this->presenter->getName();

        $category = $this->database->table('categories')->where([
            'parent_id' => $form->values->parent_id,
            'title' => $form->values->title,
        ]);

        if ($category->count() > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.categoryAlreadyExists'), 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }

        if ($form->values->title == '') {
            $this->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), 'error');
            $this->redirect(':' . $redirectTo . ':default', ['id' => null]);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function insertCategoryFormSucceeded(BootstrapUIForm $form): void
    {
        $this->database->table('pricelist_categories')->insert([
            'title' => $form->values->title,
            'parent_id' => $form->values->parent_id,
        ]);

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $categoryId = null;

        $getParams = $this->getParameters();
        unset($getParams['page']);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/CategoryControl.latte');

        if ($this->presenter->getParameter('id')) {
            $categoryId = $this->presenter->getParameter('id');
        }

        $arr['parent_id'] = $categoryId;
        $arr['pricelist_lists_id'] = $this->presenter->getParameter('pricelist');

        $template->database = $this->database;
        $template->menuList = $this->database->table('pricelist_lists')->order('title');
        $template->menu = $this->database->table('pricelist_categories')->where($arr)->order('sorted');
        $template->render();
    }

}
