<?php
/********************************************
 *
 * Arithmetic.php, created 5.8.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 * Company: ManSkal - Martin Skalický
 *
 ***************************************************************
 *
 * Contacts:
 * Core developer - petr.klimes@manskal.com
 * More info      - info@manskal.com
 * Website        - www.manskal.com
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS\Helpers\Math;

class Arithmetic
{

    public static function format_and_round(\SplFloat $number, \SplInt $precision = 2, \SplInt $decimals = 2, \SplString $dec_point = ".", \SplString $thousands_sep = " ")
    {
        return number_format(round($number, $precision), $decimals, $dec_point, $thousands_sep);
    }

    public static function from_formated(\SplString $formated_number, \SplString $dec_point = ".", \SplString $thousands_sep = " ")
    {
        $e = explode($dec_point, $formated_number);
        $e[0] = str_replace($thousands_sep, "", $e[0]);
    }

    public static function floatval($val)
    {

    }
}