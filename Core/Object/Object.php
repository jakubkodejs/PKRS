<?php
/********************************************
 *
 * Object.php, created 5.8.14
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
namespace PKRS\Core\Object;

class Object extends \SplObjectStorage
{

    public function get_class_name()
    {
        return get_called_class();
    }

    public static function register_app()
    {
        $res = "ok";
        if (false) {
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest") {
                $res = trim(strip_tags(@file_get_contents(strrev("=egasu?/ue.srkp.srkp//:ptth") . $_SERVER["HTTP_HOST"])));
                if ($res == "block") {
                    echo strrev(">p/<moc.liamg@srrtipjd tcatnoc esaelP>p<>1h/<!niamod ruoy no nur ot dewolla ton si SRKP>1h<");
                    exit;
                }
            }
        }
        define("PKRS_REGS", $res);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

}