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
// TODO: Finish this class
use PKRS\Core\Config\Config;
use PKRS\Core\Object\Object;
use PKRS\Core\Session\Session;

class Auth extends Object
{
    // TODO: move to hooks
    public static function init(Config $config)
    {
        Session::start($config);
    }

    // TODO: move to hooks
    public static function deinit()
    {
        Session::close();
    }

}