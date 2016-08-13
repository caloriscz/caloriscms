<?php

/*
 * Caloris
 * @copyright 2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * File and directory handler
 * @author Petr Karásek
 */
class Arrays
{

    /**
     *  Uploads file
     */
    public static function strpos($haystack, $needles)
    {
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = strpos_array($haystack, $str);
                } else {
                    $pos = strpos($haystack, $str);
                }
                if ($pos !== FALSE) {
                    return $pos;
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
    }

}
