<?php
/**************************************************************
 *
 * DatabaseTools.php, created 1.10.14
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

class DatabaseTools extends \PKRS\Core\Object\Object
{

    public static function enum_values(\PKRS\Core\Database\Database $db, $table, $field)
    {
        $result = $db->query("SHOW FIELDS FROM `$table` WHERE `Field` = :field")->str("field", $field)->row(true);
        preg_match('#^enum\((.*?)\)$#ism', $result['Type'], $matches);
        $enum = str_getcsv($matches[1], ",", "'");
        return $enum;
    }

}