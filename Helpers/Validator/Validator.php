<?php
/**************************************************************
 *
 * Validator.php, created 20.10.14
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
namespace PKRS\Helpers\Validator;

class Validator extends \PKRS\Core\Service\Service
{

    function is_date_format($input, $dateformat = "d.m.Y H:i:s")
    {
        $time = \DateTime::createFromFormat($dateformat, $input);
        return $time instanceof \DateTime ? true : false;
    }

    function is_empty_str($str)
    {
        return trim($str) == "";
    }

    function is_empty_POST($_POST_key)
    {
        return !isset($_POST[$_POST_key]) || trim($_POST[$_POST_key]) == "";
    }

    function is_email($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}