<?php
/**************************************************************
 *
 * AppService.php, created 3.11.14
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

// Class for externding main Controller
class AppService extends Object
{

    /* @var \PKRS\Core\Service\ServiceContainer @inject */
    private static $serviceContainer;

    /**
     * @param \PKRS\Core\Service\ServiceContainer $container
     */
    public function set_container(ServiceContainer &$container)
    {
        self::$serviceContainer = & $container;
    }

    /**
     * @return \PKRS\Core\Service\ServiceContainer
     */
    public function get_container()
    {
        return self::$serviceContainer;
    }

    /**
     * Alias for get_container
     *
     * @return \PKRS\Core\Service\ServiceContainer
     */
    public function gc()
    {
        return self::$serviceContainer;
    }

}