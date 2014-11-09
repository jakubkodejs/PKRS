<?php
/********************************************
 *
 * Handler.php, created 5.8.14
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
namespace PKRS\Core\Session;

use PKRS\Core\Config\Config;
use PKRS\Core\Service\Service;

class Session extends Service
{
    /**
     * [user] = > auth
     * [lang] = > lang
     * [messages]
     */
    public static function start(Config $config)
    {
        @session_start();
        @session_regenerate_id(true);
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
        self::gc()->get_hooks()->execute("session", "on_start",$_SESSION);
    }

    public static function close()
    {
        @session_write_close();
        self::gc()->get_hooks()->execute("session", "on_close");
    }

    public static function is_set($key)
    {
        return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
    }
}