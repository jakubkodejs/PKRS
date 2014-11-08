<?php
/**************************************************************
 *
 * php_compatibility.php, created 8.11.14
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
if (!function_exists("boolval")) {
    function boolval($var)
    {
        if (is_string($var)) {
            // hacks (for configs etc...)
            if ($var == "true") return true;
            if ($var == "yes") return true;
        }
        return (bool)!!$var;
    }
}