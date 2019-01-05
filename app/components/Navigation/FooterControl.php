<?php
namespace Caloriscz\Navigation;

use App\Forms\Contacts\NewsletterFormControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Social\FacebookControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class FooterControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSocialFacebook(): FacebookControl
    {
        return new FacebookControl();
    }

    protected function createComponentNewsletterForm(): NewsletterFormControl
    {
        return new NewsletterFormControl($this->database);
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/' . $template->settings['navigation_footer_template'] . 'Control.latte');
        $template->render();
    }

}
