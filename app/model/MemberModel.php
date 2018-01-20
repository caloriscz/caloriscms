<?php

/*
 * Caloris Members
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;
use Nette\Database\Context;

/**
 * Authenticating and authorizing users
 * @author Petr Karásek <caloris@caloris.cz>
 */
class MemberModel
{

    /** @var Context */
    public $database;

    public function __construct(Contextt $database)
    {
        $this->database = $database;
    }

    /**
     * Get member table
     * @param $user
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow
     */
    public function getTable($user)
    {
        $userDb = $this->database->table('users')->where(['username' => $user])->limit(1);

        if ($userDb->count() > 0) {
            $user = $userDb->fetch();
        } else {
            $user = FALSE;
        }

        return $user;
    }

    /**
     * Get new ID
     * @return string
     */
    public function getID()
    {
        $orderID = '';
        $menuId = $this->database->table('users')->order('uid DESC')->limit(1);
        
        foreach ($menuId as $idfCheck) {
            $orderID = $idfCheck->uid;
        }

        $idfActive = substr($orderID, 'TA');

        if (count($idfCheck) > 0) {
            $idf = str_pad($idfActive + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $idf = str_pad(0 + 1, 6, '0', STR_PAD_LEFT);
        }

        $idfComplete = 'MU' . $idf;

        return $idfComplete;
    }

    /**
     * Get user name from ID
     * @param $username
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow3
     */
    public function getUserNameID($username)
    {
        return $this->database->table('users')->where('username', $username)->fetch()->id;
    }

    /**
     * Does username exists
     * @param $username
     * @return int
     */
    public function getUserName($username)
    {
        return $this->database->table('users')->where('username', $username)->count();
    }

    /**
     * Check if user exists and user name and password is correct
     * @param $username
     * @return bool
     */
    public function getState($username)
    {
        $stateDb = $this->database->table('users')->where(array(
            'username' => $username,
        ));

        if ($stateDb->count() > 0) {
            $stateInfo = $stateDb->fetch();

            if ($stateInfo->state == 0) {
                $stateMsg = FALSE;
            } else {
                $stateMsg = TRUE;
            }
        } else {
            $stateMsg = TRUE;
        }

        return $stateMsg;
    }

    /**
     * Check if e-mail address is in database
     * @param string $email E-mail address
     * @return object
     */
    public function getEmail($email)
    {
        return $this->database->table('users')->where('email', $email)->count();
    }

    /**
     * Delete given user
     */
    public function delete($cols)
    {
        $this->database->table('users')->get($cols['id'])->delete();
    }

    /**
     * Set activation code to database
     * @param type $email
     * @param type $passwordGenerate
     */
    public function setActivation($email, $passwordGenerate)
    {
        $this->database->table('users')->where(array(
            'email' => $email
        ))->update(array(
            'activation' => $passwordGenerate)
        );
    }

}
