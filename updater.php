<?php
/**************************************************************
 *
 * updater.php, created 9.11.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
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
 **************************************************************
 *
 *  STAND-ALONE SELF UPDATING
 *
 */
define("DS",DIRECTORY_SEPARATOR);
define("INI_FILE",dirname(__FILE__).DS."updater.ini");
define("CACHE_FILE",dirname(__FILE__).DS."updater_cache.php");
define("LOG_FILE",dirname(__FILE__).DS."updater_LOG.txt");
if (file_exists(dirname(__FILE__).DS."Core".DS."_includes".DS."version.php"))
    include(dirname(__FILE__).DS."Core".DS."_includes".DS."version.php");
else
    updater_log("PKRS version file not exists!", true);
if (!file_exists(INI_FILE)){
    updater_log("Updater ini config file not exists!",true);
}
$conf = parse_ini_file(INI_FILE);
if (!isset($conf["REMOTE_CHECK"])){
    updater_log("Updater ini file corrupted!",true);
}
$cache = array("version"=>0);
// fix no cache file
if (!file_exists(CACHE_FILE)){
    updater_log("Cache file not exists, creating");
    $h = fopen(CACHE_FILE,"w+");
    fwrite($h, "<?php\nreturn ".var_export($cache, true).";");
    fclose($h);
}
$cache = include(CACHE_FILE);
// fix bad config
if (!is_array($cache) || !in_array("version", array_keys($cache))){
    $cache = array("version"=>0);
}
$remote = file_get_contents($conf["REMOTE_CHECK"]);
if (!$remote || !is_string($remote) || trim($remote)==""){
    updater_log("Empty remote response!", true);
}
$remote = @json_decode($remote, true);
if (!$remote || !is_array($remote) || !isset($remote["version"])){
    updater_log("Not valid remote response! Response must be JSON.", true);
}
if (version_compare($remote["version"],PKRS_VERSION,">")){
    updater_log("New version detected! Local: ".PKRS_VERSION." | Remote: ".$remote["version"]." (released: ".$remote["released"].")");
} else updater_log("Version is actual.");








function updater_log($message, $is_fatal = false){
    $message = date("d.m.Y H:i:s")." - ".($is_fatal ? "ERROR - " : "").$message."\n";
    if (!file_exists(LOG_FILE)){
        $h = fopen(LOG_FILE, "w+");
    } else $h = fopen(LOG_FILE, "a");
    fwrite($h, $message);
    fclose($h);
    if ($is_fatal) die($message);
    else echo $message;
};