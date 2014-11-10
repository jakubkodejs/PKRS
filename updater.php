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
/**
 * Put your email here if you can be notify on update
 */
define("UPDATER_MAIL","");















/****************************************************************
 *
 *  UPDATER LOGIC
 */
define("DS",DIRECTORY_SEPARATOR);
define("INI_FILE",dirname(__FILE__).DS."updater.ini");
define("LOG_FILE",dirname(__FILE__).DS."updater_LOG.txt");
if (file_exists(dirname(__FILE__).DS."Core".DS."_includes".DS."version.php"))
    include(dirname(__FILE__).DS."Core".DS."_includes".DS."version.php");
else
{
    updater_log("PKRS version file not exists! Files be overwritten!");
    define("PKRS_VERSION","0.0.0");
}
if (!file_exists(INI_FILE)){
    updater_log("Updater ini config file not exists!",true);
}
$conf = parse_ini_file(INI_FILE);
if (!isset($conf["REMOTE_CHECK"])){
    updater_log("Updater ini file corrupted!",true);
}
$remote = file_get_contents($conf["REMOTE_CHECK"]);
if (!$remote || !is_string($remote) || trim($remote)==""){
    updater_log("Empty remote response!", true);
}
$remote = @json_decode($remote, true);
if (!$remote || !is_array($remote) || !isset($remote["version"])){
    updater_log("Not valid remote response! Response must be JSON.", true);
}
if (version_compare($remote["version"],PKRS_VERSION,"<=")){
    updater_log("Version is actual.");
} else {
    updater_log("New version detected! Local: ".PKRS_VERSION." | Remote: ".$remote["version"]." (released: ".$remote["released"].")");
    if (!is_dir(dirname(__FILE__)."/../_backups/")){
        updater_log("Creating not existing backups directory.");
        mkdir(dirname(__FILE__)."/../_backups/");
    }
    if (!is_dir(dirname(__FILE__)."/../_versions/")){
        updater_log("Creating not existing versions directory.");
        mkdir(dirname(__FILE__)."/../_versions/");
    }
    updater_log("Creating ZIP backup");
    if (Zip(dirname(__FILE__),dirname(__FILE__)."/../_backups/PKRS-v-".str_replace(".","-",PKRS_VERSION)."---".date("Y-m-d-H-i-s").".zip")){
        updater_log("ZIP backup created");
        updater_log("Download new version ".$remote["url"]);
        $new_version_zip = dirname(__FILE__)."/../_versions/PKRS-v-".str_replace(".","-",$remote["version"])."---".date("Y-m-d-H-i-s").".zip";
        if (download_and_store($remote["url"], $new_version_zip)){
            updater_log("Downloading version ".$remote["version"]." complete");
            updater_log("Unzipping new PKRS version");
            if (zip_extract($new_version_zip,dirname(__FILE__).DS)){
                if (!is_dir(dirname(__FILE__).DS."PKRS-master")){
                    updater_log("Unziping failed", true);;
                } else {
                    // avoid self overwrite
                    unlink(dirname(__FILE__).DS."PKRS-master".DS."updater.php");
                }
                updater_log("Unzipping OK");
                updater_log("DELETING OLD CORE FILES");
                // clean PKRS directory && avoid self deletion
                foreach(scandir(dirname(__FILE__)) as $f){
                    if (in_array($f,array(".","..","PKRS-master","updater.php","updater_LOG.txt","updater.ini")))
                        continue; // skip dots && self
                    if (rrmdir($f)){
                        updater_log("DELETED ".$f);
                    } else updater_log("NOT DELETED ".$f);
                }
                updater_log("Moving files from cache");
                if (copy_files(dirname(__FILE__).DS."PKRS-master".DS, dirname(__FILE__))){

                    updater_log("Moving files from cache OK");
                    rrmdir(dirname(__FILE__).DS."PKRS-master");
                    updater_log("UPDATING COMPLETE");
                } else {
                    updater_log("Moving files from PKRS-master failed!");
                }
            } else {
                updater_log("Error in unziping files");
            }
        } else updater_log("Downloading file error", true);
    } else {
        updater_log("ZIP backup NOT created",true);
    }
}

/****************************************************************************
 *  HELPERS AND FUNCTIONS
 */
/**
 * Unzip archive
 *
 * @param $file
 * @param $extractPath
 * @return bool
 */
function zip_extract($file, $extractPath) {

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
        chmod_r($extractPath);
        return TRUE;
    } else {
        return FALSE;
    }

}
function chmod_r($Path) {
    return;

}
function copy_files($source, $destination){
    // Cycle through all source files
    //var_dump(posix_getpwuid(stat($source)["uid"]));exit;
    foreach (scandir($source) as $file) {
        if (in_array($file, array(".",".."))) continue;
        // If we copied this successfully, mark it for deletion
        if (is_dir($source.DIRECTORY_SEPARATOR.$file)){
            copy_files($source.DS.$file, $destination.DS.$file);
        }
        else if(is_file($source.DS.$file)){
            if (!is_dir(dirname($destination.DS.$file))){
                mkdir(dirname($destination.DS.$file),0755,true);
            }
            if (copy($source.DS.$file, $destination.DS.$file)) {
                $delete[] = $source.DIRECTORY_SEPARATOR.$file;
            }
        }
    }
    return true;
}
/**
 * Delete directory
 *
 * @param $dir
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        return rmdir($dir);
    } else{
        return unlink($dir);
    }
}
/**
 * Log messages and output its
 *
 * @param $message
 * @param bool $is_fatal
 */
function updater_log($message, $is_fatal = false){
    $message = date("d.m.Y H:i:s")." - ".($is_fatal ? "ERROR - " : "").$message;
    if (!file_exists(LOG_FILE)){
        $h = fopen(LOG_FILE, "w+");
    } else $h = fopen(LOG_FILE, "a");
    fwrite($h, $message.PHP_EOL);
    fclose($h);
    if ($is_fatal) die($message);
    //else echo $message."<br>";
}
function download_and_store($remote, $local){
    return file_put_contents($local, file_get_contents($remote));
}
/**
 * Create backup before updating
 *
 * @param $source
 * @param $destination
 * @return bool
 */
class ExtendedZip extends ZipArchive {

    // Member function to add a whole file system subtree to the archive
    public function addTree($dirname, $localname = '') {
        if ($localname)
            $this->addEmptyDir($localname);
        $this->_addTree($dirname, $localname);
    }

    // Internal function, to recurse
    protected function _addTree($dirname, $localname) {
        $dir = opendir($dirname);
        while ($filename = readdir($dir)) {
            // Discard . and ..
            if ($filename == '.' || $filename == '..')
                continue;

            // Proceed according to type
            $path = $dirname . '/' . $filename;
            $localpath = $localname ? ($localname . '/' . $filename) : $filename;
            if (is_dir($path)) {
                // Directory: add & recurse
                $this->addEmptyDir($localpath);
                $this->_addTree($path, $localpath);
            }
            else if (is_file($path)) {
                // File: just add
                if (filesize($path)>0)
                    $this->addFile($path, $localpath);
            }
        }
        closedir($dir);
    }

    // Helper function
    public static function zipTree($dirname, $zipFilename, $flags = 0, $localname = '') {
        $zip = new self();
        $zip->open($zipFilename, $flags);
        $zip->addTree($dirname, $localname);
        return $zip->close();
    }
}
function Zip($source, $target){
    return ExtendedZip::zipTree($source, $target, ZipArchive::CREATE);
}