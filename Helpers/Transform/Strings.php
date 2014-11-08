<?php
/**************************************************************
 *
 * Strings.php, created 4.11.14
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
namespace PKRS\Helpers\Transform;

class Strings extends \PKRS\Core\Service\Service
{

    public function create_url($string)
    {
        return preg_replace('~[^\\pL0-9_]+~u', '-', $string);
    }

}