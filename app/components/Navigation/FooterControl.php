<?php
namespace Caloriscz\Navigation;

use App\Forms\Contacts\NewsletterFormControl;
use Caloriscz\Social\FacebookControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class FooterControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentSocialFacebook()
    {
        return new FacebookControl();
    }

    protected function createComponentNewsletterForm()
    {
        return new NewsletterFormControl($this->database);
    }

    public function render()
    {
        $this->template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/FooterControl.latte');
        $this->template->render();
    }

}
