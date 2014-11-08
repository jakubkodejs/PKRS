<?php
/********************************************
 *
 * User.php, created 5.8.14
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

class User extends \PKRS\Core\Service\Service
{

    private static $self;
    private $db;
    private $config;
    private $data = array();
    var $logged = false;

    function __construct(\PKRS\Core\Session\Session $session, \PKRS\Core\Config\Config $config, \PKRS\Core\Database\Database $db)
    {
        $this->db = $db;
        $this->config = $config;
        $session::start($config);
        self::$self = $this;
    }

    public static function get_instance()
    {
        if (self::$self instanceof \PKRS\Core\User\User) {
            return self::$self;
        } else {
            return null;
        }
    }

    /**
     * @return bool (if user now logged - return true)
     */
    function check_login()
    {
        $data = null;
        $new_login = false;
        if (isset($_POST["action"]) && $_POST["action"] == "user_login" && isset($_POST["login"]) && isset($_POST["pass"])) {
            $sql = "SELECT * FROM users WHERE login=:login AND pass=:pass";
            if (defined("__OVERIDE_USER_SQL__"))
                $sql = __OVERIDE_USER_SQL__;
            $data = $this->db->query($sql)
                ->str("login", $_POST["login"])
                ->str("pass", sha1($_POST["pass"]))
                ->row(true);
            if (!$data) {
                \PKRS\Core\User\Messages::t_set("USER_NOT_FOUND", "err");
            } else {
                $new_login = true;
            }
        } else {
            // neni odeslany _post zkusim session
            if (isset($_SESSION["user"]) && is_array($_SESSION["user"]) && isset($_SESSION["user"]["login"]) && isset($_SESSION["user"]["pass"])) {
                $sql = "SELECT * FROM users WHERE login=:login AND pass=:pass";
                if (defined("__OVERIDE_USER_SQL__"))
                    $sql = __OVERIDE_USER_SQL__;
                $data = $this->db->query($sql)
                    ->str("login", $_SESSION["user"]["login"])
                    ->str("pass", $_SESSION["user"]["pass"])
                    ->row(true);
            }
        }
        if (!empty($data) && is_array($data)) {
            $this->data = $data;
            if ($this->blocked()) {
                $_SESSION["user"] = array();
                $this->data = null;
                $this->logged = false;
            } else {
                $_SESSION["user"] = $data;
                $this->logged = true;
            }
        } else {
            $_SESSION["user"] = array();
            $this->data = null;
            $this->logged = false;
        }
        return $new_login;
    }

    function blocked()
    {
        if (isset($this->data["blocked"]) && intval($this->data["blocked"]) == 1) return true;
        else return false;
    }

    public static function logout()
    {
        $_SESSION["user"] = array();
    }

    function uid()
    {
        if ($this->logged) return intval($this->data["user_id"]);
        else return 0;
    }

    function get_row()
    {
        if ($this->logged)
            return $this->data;
        else return array();
    }

}