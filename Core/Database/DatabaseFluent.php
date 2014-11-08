<?php
/********************************************
 *
 * DatabseFluent.php, created 6.8.14
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

class DatabaseFluent
{

    private $fluent;
    private $id;

    public function __construct(\PDOStatement $fluent, $id = "")
    {
        $this->fluent = $fluent;
        $this->id = $id;
        return $this;
    }

    public function fluent()
    {
        return $this->fluent;
    }

    public function int($name, $value)
    {
        $value = intval($value);
        $this->fluent->bindParam(":" . $name, $value, \PDO::PARAM_INT);
        return $this;
    }

    public function str($name, $value)
    {
        $this->fluent->bindParam(":" . $name, $value, \PDO::PARAM_STR);
        return $this;
    }

    public function execute()
    {
        $res = $this->fluent->execute();
        \PKRS\Core\Debug\Debug::log_mysql_result($res, $this->id);
        return $res;
    }

    public function row($array = false)
    {
        $this->fluent->execute();
        if ($array) {
            $res = $this->fluent->fetch(\PDO::FETCH_ASSOC);
        } else
            $res = $this->fluent->fetch(\PDO::FETCH_OBJ);
        \PKRS\Core\Debug\Debug::log_mysql_result($res, $this->id);
        return $res;
    }

    public function all($array = false)
    {
        $this->fluent->execute();
        if ($array)
            $res = $this->fluent->fetchAll(\PDO::FETCH_ASSOC);
        else
            $res = $this->fluent->fetchAll(\PDO::FETCH_OBJ);
        \PKRS\Core\Debug\Debug::log_mysql_result($res, $this->id);
        return $res;
    }

    public function one($as_int = false)
    {
        $this->fluent->execute();
        $res = $as_int ? intval($this->fluent->fetchColumn()) : $this->fluent->fetchColumn();
        \PKRS\Core\Debug\Debug::log_mysql_result($res, $this->id);
        return $res;
    }

}