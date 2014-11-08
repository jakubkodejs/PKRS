<?php
/**************************************************************
 *
 * DbConfig.php, created 4.11.14
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
namespace PKRS\Core\Config;

class DbConfig extends \PKRS\Core\Service\Service
{

    private $db;
    private $dbconfig = array();
    private $table, $key, $val;

    public function __construct()
    {
        $this->db = self::gc()->get_database();
        $config = self::gc()->get_config()->get("application");
        $this->table = $config["db_config_table"];
        $this->key = $config["db_config_key"];
        $this->val = $config["db_config_value"];
        $db_conf = $this->db->query("SELECT * FROM " . $this->table)->all(true);
        foreach ($db_conf as $conf) {
            $this->dbconfig[$conf[$this->key]] = $conf[$this->val];
        }
    }

    public function get_one($key)
    {
        return isset($this->dbconfig[$key]) ? $this->dbconfig[$key] : "";
    }

    function get_all()
    {
        return $this->dbconfig;
    }

    function update_value($key, $value)
    {
        if (in_array($key, array_keys($this->dbconfig))) {
            if (
            $this->db->query("UPDATE " . $this->table . " SET " . $this->val . "=:val WHERE " . $this->key . "=:key")
                ->str("key", $key)
                ->str("val", $value)
                ->execute()
            ) {
                $this->dbconfig[$key] = $value;
                return true;
            } else return false;
        } else {
            if (
            $this->db->query("INSERT INTO " . $this->table . " (" . $this->key . ", " . $this->val . ") VALUES (:key,:val)")
                ->str("key", $key)
                ->str("val", $value)
                ->execute()
            ) {
                $this->dbconfig[$key] = $value;
                return true;
            } else return false;
        }
    }


}