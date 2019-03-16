<?php

class Filters
{
    use \Nette\SmartObject;

    public static function common($filter, $value)
    {
        if (method_exists(__CLASS__, $filter)) {
            $args = func_get_args();
            array_shift($args);
            return call_user_func_array(array(__CLASS__, $filter), $args);
        }
    }

    public static function ago($s): string
    {
        $date = new \DateTime();
        $date->setDate(date('Y', strtotime($s)), date('m', strtotime($s)), date('d', strtotime($s)));
        $interval = $date->diff(new \DateTime('now'));

        return $interval->format('%a days');
    }

    public static function round($s, $nr = 2): float
    {
        return round($s, $nr);
    }

    public static function toMins($s): string
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

    public static function toBaseName($s): string
    {
        return basename($s);
    }

    public static function dateDiff($s, $t): string
    {
        $datetime1 = date_create($s);
        $datetime2 = date_create($t);
        $interval = date_diff($datetime1, $datetime2);

        return $interval->format('%R%a days');
    }

    public static function numericday($s)
    {
        $names = [
            1 => 'Pondělí',
            2 => 'Úterý',
            3 => 'Středa',
            4 => 'Čtvrtek',
            5 => 'Pátek',
            6 => 'Sobota',
            7 => 'Neděle'];

        return $names[$s];
    }

    public static function numericmonth($s)
    {
        $nazvy = [
            1 => 'Leden',
            2 => 'Únor',
            3 => 'Březen',
            4 => 'Duben',
            5 => 'Květen',
            6 => 'Červen',
            7 => 'Červenec',
            8 => 'Srpen',
            9 => 'Září',
            10 => 'Říjen',
            11 => 'Listopad',
            12 => 'Prosinec',
        ];

        return $nazvy[$s];
    }

}