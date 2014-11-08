<?php
/********************************************
 *
 * Handler.php, created 5.8.14
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
namespace PKRS\Core\Session;

class Session extends \PKRS\Core\Service\Service
{
    /**
     * [user] = > auth
     * [lang] = > lang
     * [messages]
     */
    public static function start(\PKRS\Core\Config\Config $config)
    {
        @session_start();
        if (!isset($_SESSION["user"]))
            $_SESSION["user"] = array();
        if (!isset($_SESSION["lang"]))
            $_SESSION["lang"] = $config->get("default_lang", "cs");
        if (!isset($_SESSION["messages"])) {
            $_SESSION["messages"] = array(
                "err" => array(),
                "ok" => array(),
                "warn" => array(),
                "info" => array()
            );
        }
    }

    public static function close()
    {
        @session_write_close();
    }

    public static function is_set($key)
    {
        return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
    }
}