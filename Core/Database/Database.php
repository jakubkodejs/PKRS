<?php
/********************************************
 *
 * Database.php, created 5.8.14
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
namespace PKRS\Core\Database;

class Database extends \PKRS\Core\Service\Service
{

    private static $selfie;
    var $conn;
    private $last_fluent = null;

    public function __construct(\PKRS\Core\Config\Config $config)
    {
        try {
            $this->conn = new \PDO($config->get("database")["type"] . ':dbname=' . $config->get("database")["name"] . ';host=' . $config->get("database")["host"], $config->get("database")["user"], $config->get("database")["pass"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        } catch (\PDOException $e) {
            throw new \PKRS\Core\Exception\DbException(__CLASS__ . ": Not connected! (" . $e->getMessage() . ")");
        }
        self::$selfie = $this;
    }

    public static function get_instance()
    {
        return self::$selfie;
    }

    public function get_connection()
    {
        return $this->conn;
    }

    /**
     * @return \PDOStatement
     */
    public function get_last_fluent()
    {
        return $this->last_fluent;
    }

    /**
     * @param $query string
     * @return \PKRS\Core\Database\DatabaseFluent
     */
    public function query($query)
    {
        $id = uniqid("query");
        \PKRS\Core\Debug\Debug::log_mysql($query, $id);
        $fluent = $this->conn->prepare($query);
        $this->last_fluent = $fluent;
        return new \PKRS\Core\Database\DatabaseFluent($fluent, $id);
    }

    /**
     * TODO:: Create pagination guery a send to fluent with param
     *
     * @param $query string
     * @return \PKRS\Core\Database\DatabaseFluent
     */
    public function query_with_pagination($query)
    {
        $id = uniqid("query");
        if (strpos(strtoupper($query), "LIMIT")>0){
            //$query = explode("LIMIT")
        }
        \PKRS\Core\Debug\Debug::log_mysql($query, $id);
        $fluent = $this->conn->prepare($query);
        $this->last_fluent = $fluent;
        return new \PKRS\Core\Database\DatabaseFluent($fluent, $id);
    }

    /**
     * @return int
     */
    public function last_id()
    {
        return intval($this->conn->lastInsertId());
    }

}
