<?php
/**************************************************************
 *
 * DateTime.php, created 4.11.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 *
 *
 ***************************************************************
 *
 * Contacts:
 * @author: Petr Klimeš <djpitrrs@gmail.com>
 * @url: http://www.pkrs.eu
 * @url: https://github.com/pitrrs/PKRS
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS\Helpers\Transform;

class DateTime extends \PKRS\Core\Service\Service
{

    function to_unix_timestamp($date_string, $date_format = "d.m.Y H:i:s")
    {
        $time = \DateTime::createFromFormat($date_format, $date_string);
        $ch = $time instanceof \DateTime ? true : false;
        if ($ch) {
            return $time->getTimestamp();
        } else return time();
    }

    function to_mysql_timestamp($unix_timestamp, $only_day = false)
    {
        return date("Y-m-d " . ($only_day ? "08:00:00" : "H:i:s"), $unix_timestamp);
    }

    function sec_to_hhmmss($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        if ($hours < 10) $hours = "0" . $hours;
        if ($minutes < 10) $minutes = "0" . $minutes;
        if ($seconds < 10) $seconds = "0" . $seconds;
        return $hours . ":" . $minutes . ":" . $seconds;
    }

    function time_string($timestamp)
    {
        $diff = $timestamp;

        //intervals in seconds
        $intervals = array(
            'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute' => 60
        );

        //now we just find the difference
        if ($diff == 0) {
            return ' Just now ';
        }

        if ($diff < 60) {
            return $diff == 1 ? $diff . ' second ' : $diff . ' seconds ';
        }

        if ($diff >= 60 && $diff < $intervals['hour']) {
            $diff = floor($diff / $intervals['minute']);
            return $diff == 1 ? $diff . ' minute ' : $diff . ' minutes ';
        }

        if ($diff >= $intervals['hour'] && $diff < $intervals['day']) {
            $diff = floor($diff / $intervals['hour']);
            return $diff == 1 ? $diff . ' hour ' : $diff . ' hours ';
        }

        if ($diff >= $intervals['day'] && $diff < $intervals['week']) {
            $diff = floor($diff / $intervals['day']);
            return $diff == 1 ? $diff . ' day ' : $diff . ' days ';
        }

        if ($diff >= $intervals['week'] && $diff < $intervals['month']) {
            $diff = floor($diff / $intervals['week']);
            return $diff == 1 ? $diff . ' week ' : $diff . ' weeks ';
        }

        if ($diff >= $intervals['month'] && $diff < $intervals['year']) {
            $diff = floor($diff / $intervals['month']);
            return $diff == 1 ? $diff . ' month ' : $diff . ' months ';
        }

        if ($diff >= $intervals['year']) {
            $diff = floor($diff / $intervals['year']);
            return $diff == 1 ? $diff . ' year ' : $diff . ' years ';
        }
    }

}