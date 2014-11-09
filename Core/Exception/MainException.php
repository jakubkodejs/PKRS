<?php
/********************************************
 *
 * MainException.php, created 5.8.14
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
namespace PKRS\Core\Exception;

use Exception;
use PKRS\Core\Headers\Status;
use PKRS\Core\Service\Service;

class MainException extends Exception
{

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        echo $message;exit;
        Status::send_code(500);
        parent::__construct($message, $code, $previous);
        try {
            Service::gc()->get_debug()->log_error($message, $code, $this);
            Service::gc()->get_hooks()->execute("application", "on_error");
        } catch (Exception $e){
            die("Master fatal error: ".$e->getMessage());
        }
    }

}