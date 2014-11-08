<?php
/********************************************
 *
 * defines.php, created 5.8.14
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
define("DEV", true);
define("MAINTENANCE", false);

define("DS", DIRECTORY_SEPARATOR);

// Application folders
define("ROOT_DIR", realpath(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . DIRECTORY_SEPARATOR);
define("APP_DIR", ROOT_DIR . "app" . DS);
define("CONFIG_DIR", APP_DIR);
define("INCLUDE_DIR", APP_DIR . "PKRS" . DS . "Core" . DS . "_includes" . DS);
define("THEMES_DIR", APP_DIR . "Themes" . DS);

// web paths to folders
define("THEMES_PATH", "/app/Themes/");
define("JS_MODULES_PATH", "/app/Modules/JSModules/");
define("JS_MODULES_DIR", ROOT_DIR . "app" . DS . "Modules" . DS . "JSModules" . DS);
define("PHP_MODULES_PATH", "/app/Modules/PHPModules/");
define("PHP_MODULES_DIR", ROOT_DIR . "app" . DS . "Modules" . DS . "PHPModules" . DS);

define("__OVERIDE_USER_SQL__", "SELECT a.*,b.name,b.identif FROM users a LEFT JOIN users_groups b ON a.group_id=b.group_id WHERE login=:login AND pass=:pass");

define("IS_AJAX", (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest"));