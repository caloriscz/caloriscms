<?php
namespace App\Model;

/**
 * File and directory handler
 * @author Petr Karásek
 */
class Arrays
{

    /**
     *  Uploads file
     * @param $haystack
     * @param $needles
     * @return bool|int
     */
    public static function strpos($haystack, $needles)
    {
        if (\is_array($needles)) {
            foreach ($needles as $str) {
                $pos = \is_array($str) ? strpos_array($haystack, $str) : strpos($haystack, $str);

                if ($pos !== false) {
                    return $pos;
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
    }

}
