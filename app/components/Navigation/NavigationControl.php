<?php

namespace Caloriscz\Navigation;

use App\Forms\Pages\SearchControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\NavbarMenuControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class NavigationControl extends Control
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearch(): SearchControl
    {
        return new SearchControl($this->database);
    }

    protected function createComponentNavbarMenu(): NavbarMenuControl
    {
        return new NavbarMenuControl($this->database);
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    public function handleChangeLocale($locale): void
    {
        $this->presenter->translator->setLocale($locale);

        // Language support > 1 = multilanguage else only one langauge
        $languages = $this->database->table('languages')->where('default = 1 AND code = ?', $this->presenter->translator->getLocale());

        if ($languages->count() === 0) {
            $this->presenter->redirectUrl('/' . $locale);
        } else {
            $this->presenter->redirectUrl('/');
        }
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;

        $languageSelected = $this->database->table('languages')->where('used = 1 AND code = ', $this->presenter->translator->getLocale());

        if ($languageSelected->count() > 0) {
            $template->langSelected = $languageSelected->fetch();
        } else {
            $template->langSelected = false;
        }


        $template->languages = $this->database->table('languages')->where('used = 1 AND NOT code = ', $this->presenter->translator->getLocale());
        $template->user = $this->presenter->user;

        if (isset($this->presenter->template->member)) {
            $template->member = $this->presenter->template->member;
        }

        $template->args = $this->presenter->getParameters(true);
        $template->setFile(__DIR__ . '/' . $template->settings['navigation_template'] . 'Control.latte');
        $template->render();
    }

    /**
     * Forces control to repaint.
     * @return void
     */
    function redrawControl()
    {
        // TODO: Implement redrawControl() method.
    }
}