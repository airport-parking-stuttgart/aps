<?php

class Restrictions
{
    static function stringifyDates($restrictions)
    {
        $dates = '';
        foreach ($restrictions as $restriction) {
            $dates .= $restriction->date . ' ' . $restriction->time;
            if (next($restrictions)) {
                $dates .= ',';
            }
        }

        return $dates;
    }
}