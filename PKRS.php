<?php
/**************************************************************
 *
 * PKRS.php, created 7.11.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 * Company: ManSkal - Martin Skalický
 *
 ***************************************************************
 *
 * Contacts:
 * Core developer - petr.klimes@manskal.com
 * More info      - info@manskal.com
 * Website        - www.manskal.com
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS;

use PKRS\Core\Service\ServiceContainer;

class PKRS
{

    private $service;

    public function __construct($ini_config_file)
    {
        $this->service = new ServiceContainer($ini_config_file);
    }

    public function service()
    {
        return $this->service;
    }

    public function config()
    {
        return $this->service->get_config();
    }

    public function run()
    {
        $app = new \PKRS\Core\Application($this->service);
        $app->run();
    }

}