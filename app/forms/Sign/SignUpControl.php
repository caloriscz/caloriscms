<?php

namespace App\Forms\Sign;

use App\Model\Document;
use App\Model\Helpdesk;
use App\Model\IO;
use App\Model\MemberModel;
use h4kuna\Ares\Ares;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Mail\SmtpException;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Validators;

class SignUpControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return BootstrapUIForm
     */
    public function createComponentSignUpForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);

        $form->addText('email')
            ->addRule(Form::EMAIL, 'messages.sign.enterValidEmail')
            ->setRequired('messages.sign.enterValidEmail');
        $form->addText('username')
            ->setOption('description', (string)'messages.sign.descriptionSymbolsEnabled')
            ->setRequired('messages.sign.enterValidUserName')
            ->addRule(Form::PATTERN, 'messages.sign.descriptionSymbolsEnabled', '[a-z0-9-]+')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.enterUserNameMinimum', 5)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.enterUserNameMaximum', 40);

        $groups = $this->database->table('users_categories')->fetchPairs('id', 'title');

        $form->addSelect('group', '', $groups);

        $form->addPassword('pwd')
            ->setRequired('messages.sign.enterValidPassword')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.enterPasswordMinimum', 6)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.enterPasswordMaximum', 40);

        $form->addPassword('pwd2')
            ->setRequired('messages.sign.enterEmailForCheck')
            ->addRule(Form::EQUAL, 'messages.sign.passwords-not-same', $form['pwd']);

        $form->addText('name')
            ->setRequired('messages.sign.enterValidName')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.EnterValidName', 3)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.EnterValidName', 200);
        $form->addText('street')
            ->setRequired('messages.sign.enterValidStreet')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.EnterValidStreet', 3)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.EnterValidStreet', 200);
        $form->addText('zip')
            ->setRequired('messages.sign.enterValidZip')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.EnterValidZip', 3)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.EnterValidZip', 20);
        $form->addText('city')
            ->setRequired('messages.sign.enterValidCity')
            ->addRule(Form::MIN_LENGTH, 'messages.sign.EnterValidCity', 1)
            ->addRule(Form::MAX_LENGTH, 'messages.sign.EnterValidCity', 80);

        $form->addText('company');
        $form->addText('vatin');
        $form->addText('vatid');
        $form->addText('message');


        $form->addCheckbox('newsletter', '\xC2\xA0' . 'messages.sign.newsletterCheck')
            ->setDefaultValue(TRUE);
        $form->addCheckbox('confirmation', '\xC2\xA0' . 'messages.sign.agreeWithConditions')
            ->setRequired('messages.sign.mustAgreeConditions');

        $form->setDefaults([
            'username' => $this->presenter->getParameter('user'),
            'email' => $this->presenter->getParameter('email'),
            'newsletter' => $this->presenter->getParameter('newsletter'),
            'name' => $this->presenter->getParameter('name'),
            'street' => $this->presenter->getParameter('street'),
            'city' => $this->presenter->getParameter('city'),
            'zip' => $this->presenter->getParameter('zip'),
        ]);

        $form->addSubmit('submit', 'dictionary.main.Signup')
            ->setAttribute('class', 'btn-lg btn-cart-in');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];
        $form->onValidate[] = [$this, 'signUpFormValidated'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    public function signUpFormValidated(BootstrapUIForm $form): void
    {
        $member = new MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        $formVal = $form->getValues(true);

        if ($userExists > 0 && $this->getPresenter()->template->settings['members_username_as_email'] === 0) {
            unset($formVal['username']);
            $this->getPresenter()->flashMessage('messages.sign.userNameAlreadyExists', 'error');
        } elseif (Validators::isEmail($form->values->email) === false) {
            unset($formVal['email']);
            $this->getPresenter()->flashMessage('messages.sign.enterValidEmail', 'error');
        } elseif ($emailExists > 0) {
            unset($formVal['email']);
            $this->getPresenter()->flashMessage('messages.sign.emailAlreadyExists', 'error');
        } elseif (\strlen($form->values->name) < 2) {
            $this->getPresenter()->flashMessage('messages.sign.enterValidName', 'error');
        } else {
            $msg = 1;
        }

        if ($msg !== 1) {
            unset($formVal['pwd'], $formVal['pwd2'], $formVal['confirmation']);

            $this->getPresenter()->redirect('this', $formVal);
        }
    }

    /**
     * @param BootstrapUIForm $form
     * @return BootstrapUIForm
     * @throws \h4kuna\Ares\IdentificationNumberNotFoundException
     */
    public function signUpFormSucceeded(BootstrapUIForm $form): BootstrapUIForm
    {
        $activationCode = Random::generate(12, '987654321zyxwvutsrqponmlkjihgfedcba');
        $password = Passwords::hash($form->values->pwd);

        if ($this->getPresenter()->template->settings['members_username_as_email']) {
            $username = $form->values->email;
        } else {
            $username = $form->values->username;
        }

        $arr = [
            'email' => $form->values->email,
            'username' => $username,
            'password' => $password,
            'activation' => $activationCode,
            'newsletter' => (bool)$form->values->newsletter,
            'state' => 0,
            'users_roles_id' => 4,
            'date_created' => date('Y-m-d H:i:s')
        ];

        $userId = $this->database->table('users')->insert($arr);

        if ($this->getPresenter()->template->settings['members:signup:contactEnabled']) {
            $arrContacts = [
                'contacts_categories_id' => 8,
                'users_id' => $userId,
                'name' => $form->values->name,
                'street' => $form->values->street,
                'city' => $form->values->city,
                'zip' => $form->values->zip,
                'countries_id' => 1,
            ];

            if ($this->getPresenter()->template->settings['members:signup:companyEnabled']) {
                $arrContacts['company'] = $form->values->company;
                $arrContacts['vatin'] = $form->values->vatin;
                $arrContacts['vatid'] = $form->values->vatid;
            }

            $contactId = $this->database->table('contacts')->insert($arrContacts);
            $this->database->table('contacts')->get($contactId)->update(['order' => $contactId]);
        }

        $params = [
            'username' => $form->values->username,
            'activationCode' => $activationCode,
            'settings' => $this->getPresenter()->template->settings,
            'form' => $form
        ];

        try {
            $helpdesk = new Helpdesk($this->database, $this->getPresenter()->mailer);

            if ($this->getPresenter()->template->settings['members:signup:confirmByAdmin']) {
                $helpdesk->setId(12);
                $message = 'messages.sign.signupSuccessfulLoginWhenVerified';
            } else {
                $helpdesk->setId(3);
                $message = 'messages.sign.SignupSuccessfulCanLogin';
            }

            $helpdesk->setEmail($form->values->email);
            $helpdesk->setSettings($this->getPresenter()->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send(true);

            $this->onSave('/uspesna-registrace', $message, 'note');

        } catch (SmtpException $e) {
            $this->onSave('/registrace', 'messages.sign.EmailNotSent' . $e->getMessage(), 'error');
        }
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/SignUpControl.latte');
        $template->settings = $this->getPresenter()->template->settings;
        $template->render();
    }

}
