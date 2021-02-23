<?php

namespace App\Model;

use Nette;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;


/**
 * Users authenticator.
 */
class MyAuthenticator implements Nette\Security\Authenticator
{

    private $database;
    private $passwords;

    public function __construct(Nette\Database\Explorer $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->database->table('users')->where('username', $username)->fetch();

        if (!$row) {
            throw new AuthenticationException('Nesprávné heslo.', self::IDENTITY_NOT_FOUND);
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
        }

        return new SimpleIdentity($row->id, $row->users_roles_id);
    }

}
