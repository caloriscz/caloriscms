<?php

namespace App\Model;

use Nette,
    Nette\Security;

/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator
{

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->database->table('users')->where('username', $username)->fetch();

        if (!$row) {
            throw new Security\AuthenticationException('Nesprávné heslo.', self::IDENTITY_NOT_FOUND);
        } elseif (!Security\Passwords::verify($password, $row->password)) {
            throw new Security\AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
        }

        $arr = $row->toArray();
        unset($arr['password']);
        return new Security\Identity($row->id, $row->role, $arr);
    }

}
