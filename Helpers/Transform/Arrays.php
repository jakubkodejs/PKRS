<?php
/**************************************************************
 *
 * Transform.php, created 20.10.14
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
namespace PKRS\Helpers\Transform;

class Arrays extends \PKRS\Core\Service\Service
{

    function diverse_FILES($vector)
    {
        $result = array();
        foreach ($vector as $key1 => $value1)
            foreach ($value1 as $key2 => $value2)
                $result[$key2][$key1] = $value2;
        return $result;
    }

}