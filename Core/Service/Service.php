<?php
/**************************************************************
 *
 * Service.php, created 4.11.14
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
namespace PKRS\Core\Service;

use PKRS\Core\Object\Object;

class Service extends Object
{

    private static $service_container;

    public static function set_container(ServiceContainer $serviceContainer)
    {
        self::$service_container = $serviceContainer;
    }

    /**
     * @return ServiceContainer
     */
    public static function gc()
    {
        return self::$service_container;
    }
}