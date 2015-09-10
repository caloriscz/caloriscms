<?php

namespace Nette\Forms;

/**
 * Simple Nette form renderer with supporting Twitter Bootstrap 3
 */
class BootstrapUIForm extends \Nette\Application\UI\Form
{

    public function __construct()
    {
        parent::__construct();
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = '';
        $renderer->wrappers['pair']['container'] = 'div class="form-group"';
        $renderer->wrappers['label']['container'] = 'label class="col-sm-3 control-label"';
        $renderer->wrappers['control']['container'] = 'div class="col-sm-8"';
        $renderer->wrappers['control']['.submit'] = "btn btn-default";
        $renderer->wrappers['control']['.text'] = "form-control";
        $renderer->wrappers['control']['.select'] = "form-control";
        $renderer->wrappers['control']['.password'] = "form-control";
        $renderer->wrappers['control']['.email'] = "form-control";

        $form = $this->getForm();

        $form->getElementPrototype()->class('form-horizontal');

        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->setAttribute('class', empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->setAttribute('class', 'form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->class($control->getControlPrototype()->type);
            }
        }
    }

}

class FilterForm extends \Nette\Application\UI\Form
{

    public function __construct()
    {
        parent::__construct();
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = '';
        $renderer->wrappers['pair']['container'] = '';
        $renderer->wrappers['label']['container'] = '';
        $renderer->wrappers['control']['container'] = '';
        $renderer->wrappers['control']['.submit'] = 'btn';
    }

}