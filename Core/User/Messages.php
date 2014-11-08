<?php
/********************************************
 *
 * Messages.php, created 8.8.14
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
namespace PKRS\Core\User;

class Messages extends \PKRS\Core\Service\Service
{

    /**
     * types = ok | err | warn
     *
     * @param $message
     * @param string $type
     */
    public static function set($message, $type = "ok")
    {
        if (trim($message) == "") return;
        if (!in_array($type, array("ok", "err", "warn")))
            throw new \Exception("Message type has been only ok, err and warn");
        if (!isset($_SESSION["messages"])) $_SESSION["messages"] = array();
        if (!isset($_SESSION["messages"][$type])) $_SESSION["messages"][$type] = array();
        if (!in_array($message, $_SESSION["messages"][$type]))
            $_SESSION["messages"][$type][] = $message;
    }

    public static function get_all_imploded($glue = "<br><br>")
    {
        if (!isset($_SESSION["messages"])) return array();
        $arr = array();
        if (isset($_SESSION["messages"]["ok"]) && !empty($_SESSION["messages"]["ok"])) $arr["ok"] = implode($glue, $_SESSION["messages"]["ok"]);
        if (isset($_SESSION["messages"]["err"]) && !empty($_SESSION["messages"]["err"])) $arr["err"] = implode($glue, $_SESSION["messages"]["err"]);
        if (isset($_SESSION["messages"]["warn"]) && !empty($_SESSION["messages"]["warn"])) $arr["warn"] = implode($glue, $_SESSION["messages"]["warn"]);
        self::clear_all();
        return $arr;
    }

    public static function t_set($message, $type = "ok")
    {
        $message = \PKRS\Core\Lang\Lang::xlate($message);
        if (!in_array($type, array("ok", "err", "warn")))
            throw new \Exception("Message type has been only ok, err and warn");
        if (!isset($_SESSION["messages"])) $_SESSION["messages"] = array();
        if (!isset($_SESSION["messages"][$type])) $_SESSION["messages"][$type] = array();
        if (!in_array($message, $_SESSION["messages"][$type]))
            $_SESSION["messages"][$type][] = $message;
    }

    public static function get_as_str($type, $glue = "<br>")
    {
        if (!in_array($type, array("ok", "err", "warn")))
            throw new \Exception("Message type has benn only ok, err and warn");
        if (!isset($_SESSION["messages"])) return "";
        if (!isset($_SESSION["messages"][$type])) return "";
        return implode($glue, $_SESSION["messages"][$type]);
    }

    public static function clear_all()
    {
        $_SESSION["messages"] = array();
    }

    public static function has_messages($type)
    {
        return !empty($_SESSION["messages"][$type]);
    }


}