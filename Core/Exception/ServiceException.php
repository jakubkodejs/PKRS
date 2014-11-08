<?php
/**************************************************************
 *
 * ServiceException.php, created 3.11.14
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

class ServiceException extends MainException
{

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}