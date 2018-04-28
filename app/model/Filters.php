<?php

class Filters extends \Nette\Object
{
    public static function common($filter, $value)
    {
        if (method_exists(__CLASS__, $filter)) {
            $args = func_get_args();
            array_shift($args);
            return call_user_func_array(array(__CLASS__, $filter), $args);
        }
    }

    public static function ago($s)
    {
        $date = new \DateTime();
        $date->setDate(date('Y', strtotime($s)), date('m', strtotime($s)), date('d', strtotime($s)));
        $interval = $date->diff(new \DateTime('now'));

        return $interval->format('%a days');
    }

    public static function round($s, $nr = 2)
    {
        return round($s, $nr);
    }

    public static function toMins($s)
    {
        if ($s < 60 && $s > 0) {
            $duration = '0:' . $s . '.';
        } elseif ($s >= 60) {
            $duration = ceil($s / 60) . ':' . ($s % 60) . '.';
        } else {
            $duration = '-';
        }

        return $duration;
    }

    public static function toBaseName($s)
    {
        return basename($s);
    }

    public static function dateDiff($s, $t)
    {
        $datetime1 = date_create($s);
        $datetime2 = date_create($t);
        $interval = date_diff($datetime1, $datetime2);

        return $interval->format('%R%a days');
    }

    public static function numericday($s)
    {
        $names = [
            1 => 'dictionary.days.Monday',
            2 => 'dictionary.days.Tuesday',
            3 => 'dictionary.days.Wednesday',
            4 => 'dictionary.days.Thursday',
            5 => 'dictionary.days.Friday',
            6 => 'dictionary.days.Saturday',
            7 => 'dictionary.days.Sunday'];

        return $names[$s];
    }

    public static function numericmonth($s)
    {
        $nazvy = [
            1 => 'dictionary.months.January',
            2 => 'dictionary.months.February',
            3 => 'dictionary.months.March',
            4 => 'dictionary.months.April',
            5 => 'dictionary.months.May',
            6 => 'dictionary.months.June',
            7 => 'dictionary.months.July',
            8 => 'dictionary.months.August',
            9 => 'dictionary.months.September',
            10 => 'dictionary.months.October',
            11 => 'dictionary.months.November',
            12 => 'dictionary.months.December',
        ];

        return $nazvy[$s];
    }

}