<?php
/********************************************
 *
 * autoload.php, created 5.8.14
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

function app_loader($name)
{
    if (trim($name) == "") return;
    $nn = $name;
    if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . "PHPMailer" . DS . "PHPMailerAutoload.php"))
        require(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . "PHPMailer" . DS . "PHPMailerAutoload.php");
    if (class_exists($name)) {
        return;
    }
    $finded = false;
    $parts = explode("\\", trim($name, "\\"));
    $dir = APP_DIR . implode(DS, $parts);
    if (file_exists($dir . ".php")) {
        include($dir . ".php");
        $finded = true;
    }
    if (!$finded) {
        if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php")) {

            include APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php";
            $finded = true;
        } else if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php")) {
            include(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php");
            $finded = true;
        } else {
            $finded = false;
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(APP_DIR . DS . "PKRS" . DS . "Vendor", RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );
            $name = $name . ".php";
            foreach ($iter as $file) {
                if ($file->getFilename() == $name) {
                    include($file->getPathname());
                    $finded = true;
                    break;
                } else
                    if (trim(strtolower($file->getFilename())) == trim(strtolower($name))) {
                        include($file->getPathname());
                        $finded = true;
                        break;
                    }
            }
        }
        if (!$finded) {
            die("Class " . $nn . " not found!!");
        }
    }
}

try {
    spl_autoload_register("app_loader", true);
} catch (Exception $e) {
    die($e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")");
}
