<?php
/********************************************
 *
 * MainException.php, created 5.8.14
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
namespace PKRS\Core\Exception;

class MainException extends \Exception
{

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        \PKRS\Core\Service\Service::gc()->get_debug()->log_error($message, $code, $this);
        \PKRS\Core\Service\Service::gc()->get_hooks()->execute("application", "on_error");
    }

}