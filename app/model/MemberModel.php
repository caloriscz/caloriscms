<?php

/*
 * Caloris Members
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Authenticating and authorizing users
 * @author Petr Karásek <caloris@caloris.cz>
 */
class MemberModel
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get member table
     * @param string $user User name
     */
    function getTable($user)
    {
        $userDb = $this->database->table('users')->where(array("username" => $user))->limit(1);

        if ($userDb->count() > 0) {
            $user = $userDb->fetch();
        } else {
            $user = FALSE;
        }

        return $user;
    }

    /**
     * Get new ID
     * @param string $username User name
     */
    function getID()
    {
        foreach ($this->database->table('users')->order("uid DESC")->limit(1) as $idfCheck) {
            $orderID = $idfCheck->uid;
        }

        $idfActive = substr($orderID, "TA");

        if (count($idfCheck) > 0) {
            $idf = str_pad(($idfActive + 1), 6, "0", STR_PAD_LEFT);
        } else {
            $idf = str_pad((0 + 1), 6, "0", STR_PAD_LEFT);
        }

        $idfComplete = "MU" . $idf;

        return $idfComplete;
    }

    /**
     * Get user name from ID
     * @param string $username User name
     */
    function getUserNameID($username)
    {
        return $this->database->table('users')->where('username', $username)->fetch()->id;
    }

    /**
     * Does username exists
     * @param string $username User name
     */
    function getUserName($username)
    {
        return $this->database->table('users')->where('username', $username)->count();
    }

    /**
     * Check if user exists and user name and password is correct
     * @param string $username User name
     */
    function getState($username)
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
    function getEmail($email)
    {
        return $this->database->table('users')->where('email', $email)->count();
    }

    /**
     * Delete given user
     */
    function delete($cols)
    {
        $this->database->table("users")->get($cols["id"])->delete();
    }

    /**
     * Set activation code to database
     * @param type $email
     * @param type $passwordGenerate
     */
    function setActivation($email, $passwordGenerate)
    {
        $this->database->table("users")->where(array(
            'email' => $email
        ))->update(array(
            "activation" => $passwordGenerate)
        );
    }

}
