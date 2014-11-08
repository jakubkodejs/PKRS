<?php
/********************************************
 *
 * birth.php, created 5.8.14
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
@ob_start();
if (!defined("APP_START"))
    define("APP_START", microtime(true));
if (defined("DEV") && DEV === true) {
    error_reporting(E_ALL);
    ini_set("display_error", 1);
} else {
    error_reporting(false);
    ini_set("display_error", 0);
}
// Maintenance
if (defined("MAINTENANCE") && MAINTENANCE === true) {
    die("Maintenance mode. Please try again later.");
}
include(dirname(__FILE__) . DS . "php_compatibility.php");
include(INCLUDE_DIR . "autoload.php");
mb_internal_encoding("UTF-8");
function pkrs_error_handler($err, $str, $file, $line, $context)
{
    if (!IS_AJAX) \PKRS\Core\Debug\Debug::handler($err, $str, $file, $line, $context);
    else \PKRS\Core\Debug\Debug::handler_ajax($err, $str, $file, $line, $context);
    return true;
}

;
/**
 * Send to debug
 *
 * @param $var
 */
function _d($name, $var)
{
    \PKRS\Core\Debug\Debug::add_dump($name, $var);
}

$php_old_error_handler = set_error_handler("pkrs_error_handler", E_ALL);
