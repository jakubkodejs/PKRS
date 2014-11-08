<?php
/********************************************
 *
 * Redirects.php, created 6.8.14
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
namespace PKRS\Core\Headers;

class Redirects extends \PKRS\Core\Object\Object
{

    public static function e404($base_path = "/", $route = "404")
    {
        self::log_redirect($base_path . $route . " (HTTP code 404)");
        header("location:" . $base_path . $route, null, 404);
        exit();
    }

    public static function redirect($path)
    {
        self::log_redirect($path);
        header("location:" . $path);
        exit;
    }

    private static function log_redirect($path)
    {
        if (!IS_AJAX) {
            if (isset($_SESSION)) {
                if (!isset($_SESSION["redirect_log"])) $_SESSION["redirect_log"] = array();
                $_SESSION["redirect_log"][] = array(
                    "from_path" => $_SERVER["REQUEST_URI"],
                    "to_path" => $path,
                    "SERVER" => $_SERVER,
                    "SESSION" => $_SESSION
                );
            }
        }
    }

}