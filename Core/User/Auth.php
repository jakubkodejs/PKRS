<?php
/********************************************
 *
 * Auth.php, created 5.8.14
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
namespace PKRS\Core\User;

class Auth extends \PKRS\Core\Object\Object
{

    public static function init(\PKRS\Core\Config\Config $config)
    {
        \PKRS\Core\Session\Session::start($config);
    }

    public static function deinit()
    {
        \PKRS\Core\Session\Session::close();
        if (!defined("PKRS_REGS")) {
            ob_clean();
            echo strrev(">p/<moc.liamg@srrtipjd tcatnoc esaelP>p<>1h/<!niamod ruoy no nur ot dewolla ton si SRKP>1h<");
            exit;
        }
    }

}