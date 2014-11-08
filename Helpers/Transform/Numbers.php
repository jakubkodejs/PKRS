<?php
/**************************************************************
 *
 * Numbers.php, created 4.11.14
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

class Numbers extends \PKRS\Core\Service\Service
{

    function limit_between($val, $min, $max)
    {
        if ($val < $min) $val = $min;
        if ($val > $max) $val = $max;
        return $val;
    }

    function number_format($number)
    {
        return number_format($number, 2, ".", " ");
    }

    function human_filesize($bytes, $decimals = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$size[$factor];
    }

}