<?php
/**************************************************************
 *
 * TestUser.php, created 3.11.14
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

class TestUser
{

    public function __construct(\PKRS\Core\Database\Database &$db)
    {
        var_dump($db);
        echo "TEST USER!!!";
        exit;
    }

}