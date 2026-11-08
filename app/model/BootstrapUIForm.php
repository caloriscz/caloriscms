<?php

namespace Nette\Forms;

use Nette\Application\UI\Form;

/**
 * Simple Nette form renderer with supporting Twitter Bootstrap 3
 */
class BootstrapUIForm extends Form
{

    public function __construct()
    {
        parent::__construct();
        // Protect every admin POST form, including nested forms and uploads.
        // Attach after factory defaults so existing explicit protection is retained.
        $this->onAnchor[] = static function (self $form): void {
            if ($form->getPresenter() instanceof \App\AdminModule\Presenters\BasePresenter
                && $form->isMethod('post')) {
                $control = $form->getComponent('_token_', false)
                    ?? $form->addProtection('The form expired. Please try again.');
                if ($form->isSubmitted()) {
                    $control->loadHttpData();
                    // Reject before legacy onValidate callbacks read values or
                    // perform side effects on a form that failed CSRF validation.
                    if (!Controls\CsrfProtection::validateCsrf($control)) {
                        throw new \Nette\Application\ForbiddenRequestException('Invalid form token.');
                    }
                }
            }
        };
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = '';
        $renderer->wrappers['pair']['container'] = 'div class="form-group"';
        $renderer->wrappers['label']['container'] = 'label class="col-sm-3 control-label"';
        $renderer->wrappers['control']['container'] = 'div class="col-sm-8"';
        $renderer->wrappers['control']['.submit'] = 'btn btn-default';
        $renderer->wrappers['control']['.text'] = 'form-control';
        $renderer->wrappers['control']['.select'] = 'form-control';
        $renderer->wrappers['control']['.password'] = 'form-control';
        $renderer->wrappers['control']['.email'] = 'form-control';

        $form = $this->getForm();

        $form->getElementPrototype()->class('form-horizontal');

        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->setHtmlAttribute('class', empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->setHtmlAttribute('class', 'form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->class($control->getControlPrototype()->type);
            }
        }
    }

}
