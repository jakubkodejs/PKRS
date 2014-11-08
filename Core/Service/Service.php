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

class Service extends \PKRS\Core\Object\Object
{

    private static $service_container;

    public static function set_containr(\PKRS\Core\Service\ServiceContainer $serviceContainer)
    {
        self::$service_container = $serviceContainer;
    }

    /**
     * @return \PKRS\Core\Service\ServiceContainer
     */
    public static function gc()
    {
        return self::$service_container;
    }
}