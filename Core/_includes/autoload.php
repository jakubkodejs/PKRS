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
    if (class_exists($name, true)) {
        return;
    }
    if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . "PHPMailer" . DS . "PHPMailerAutoload.php"))
        require(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . "PHPMailer" . DS . "PHPMailerAutoload.php");
    $parts = explode("\\", trim($name, "\\"));
    $dir = APP_DIR . implode(DS, $parts);
    if (file_exists($dir . ".php")) {
        include_once($dir . ".php");
        return;
    }
    if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php")) {
        include_once APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php";
        return;
    } else if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php")) {
        include_once(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php");
        return;
    } else {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(APP_DIR . DS . "PKRS" . DS . "Vendor", RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach ($iter as $file) {
            if ($file->getFilename() == $name. ".php") {
                include_once($file->getPathname());
                return;
            } else
                if (trim(strtolower($file->getFilename())) == trim(strtolower($name. ".php"))) {
                    include_once($file->getPathname());
                    return;
                }
        }
    }
    // vendor tests
    // Using namespace?
    if (is_int(strpos($name, "\\"))) {
        $name = @end(explode("\\", $name));
    }
    if (class_exists($name, true)) {
        return;
    }
    if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php")) {
        include_once APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".php";
        return;
    } else if (file_exists(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php")) {
        include_once(APP_DIR . DS . "PKRS" . DS . "Vendor" . DS . $name . DS . $name . ".class.php");
        return;
    } else {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(APP_DIR . DS . "PKRS" . DS . "Vendor", RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach ($iter as $file) {
            if ($file->getFilename() == $name. ".php") {
                include_once($file->getPathname());
                return;
            } else
                if (trim(strtolower($file->getFilename())) == trim(strtolower($name. ".php"))) {
                    include_once($file->getPathname());
                    return;
                }
        }
    }

    die("Class " . $name ."($nn)" . " not found!!");


}
try {
    spl_autoload_register("app_loader", true);
} catch (Exception $e) {
    die($e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")");
}
