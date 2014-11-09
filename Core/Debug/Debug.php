<?php
/********************************************
 *
 * Debug.php, created 5.8.14
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
namespace PKRS\Core\Debug;
// TODO: Improve this class
class Debug extends \PKRS\Core\Service\Service
{

    static $enabled = false;
    static $logs = array();
    static $mysql = array();
    static $ajax = array();
    static $detailed = array();
    static $header = false;
    static $footer = false;
    static $dumps = array();

    public function __construct()
    {
        self::gc()->get_hooks()->register_action("application", "on_error", array("\\PKRS\\Core\\Debug\\Debug", "show"), array(), 0);
        self::gc()->get_hooks()->register_action("application", "on_exit", array("\\PKRS\\Core\\Debug\\Debug", "show"), array(), 0);
        if (self::gc()->get_config()->get("application")["debug"] == "true" && !IS_AJAX) {
            self::$enabled = true;
        }
    }

    static function add_dump($name, $var)
    {
        self::$dumps[] = array("name" => $name, "var" => $var);
    }

    static function log_mysql($query, $id)
    {
        self::$mysql[$id] = array("query" => $query, "result" => null, "time" => microtime(true));
    }

    static function log_mysql_result($result, $id)
    {
        self::$mysql[$id]["result"] = $result;
        self::$mysql[$id]["time"] = round((microtime(true) - self::$mysql[$id]["time"]), 4);
    }

    static function log($message)
    {
        self::$logs["normal"][] = $message;
    }

    static function log_error($message, $code, \Exception $exception)
    {
        self::$logs["error"][] = array("m" => $message, "c" => $code, "e" => $exception);
    }

    static function enable()
    {
        self::$enabled = true;
    }

    static function handler_ajax($errno, $errstr, $errfile, $errline, $context = array())
    {
        self::$ajax[] = array("errno" => $errno, "errstr" => $errstr, "file" => $errfile, "line" => $errline, "context" => $context);
    }

    static function handler($errno, $errstr, $errfile, $errline, $context = array())
    {
        if (IS_AJAX) return;
        self::$detailed[] = array("errno" => $errno, "errstr" => $errstr, "file" => $errfile, "line" => $errline, "context" => $context);
        if (self::$enabled) {
            if ($errno != E_PARSE || $errno != E_ERROR) {
                return;
            }
            if (!self::$header) {
                ob_get_clean();
                self::$header = true;
                echo "<html><head><title>PKRS Error page</title>";
                echo "<style type='text/css'>" . file_get_contents(dirname(__FILE__) . DS . "debug.css") . "</style>";
                echo "<script type='text/javascript'>" . file_get_contents(dirname(__FILE__) . DS . "debug.js") . "</script>";
                echo "</head><body class='debug_page'><h1>PKRS Debugger :: Error occured!</h1>";
            }
            echo "<div class='debugger_cont'>";
            echo "<h2>Stack trace:</h2>";
            self::echo_stack(true, self::FriendlyErrorType($errno) . ": " . $errstr, $errfile, $errline, "", "", "");
            foreach (debug_backtrace() as $trace) {
                self::echo_stack(false, "", @$trace["file"], @$trace["line"], @$trace["class"], @$trace["class"], @$trace["args"]);
            }
            echo "</div>";
            if (!self::$footer) {
                self::$footer = true;
                self::echo_server_usage();
            }
            exit;
        } else {
            if ($errno == E_PARSE || $errno == E_ERROR) {
                echo "<h1>Sorry, here is error :(</h1>";
                exit;
            }
            return true;
        }
        return true;
    }

    static function show()
    {
        if (IS_AJAX) return;
        if (self::$enabled) {
            if (!empty(self::$logs["error"])) {

                if (!self::$header) {
                    ob_get_clean();
                    self::$header = true;
                    echo "<html><head><title>PKRS Error page</title>";
                    echo "<style type='text/css'>" . file_get_contents(dirname(__FILE__) . DS . "debug.css") . "</style>";
                    echo "<script type='text/javascript'>" . file_get_contents(dirname(__FILE__) . DS . "debug.js") . "</script>";
                    echo "</head><body class='debug_page'><h1>PKRS Debugger :: Error occured!</h1>";
                }

                echo "<div class='debugger_cont'>";
                foreach (self::$logs["error"] as $e) {
                    echo "<h2>Stack trace:</h2>";
                    $i = 1;
                    self::echo_stack(true, self::FriendlyErrorType($e["e"]->getCode()) . ": " . $e["e"]->getMessage(), $e["e"]->getFile(), $e["e"]->getLine(), "", "", "");
                    foreach ($e["e"]->getTrace() as $trace) {
                        self::echo_stack(false, "", isset($trace["file"]) ? $trace["file"] : "", isset($trace["line"]) ? $trace["line"] : "", isset($trace["function"]) ? $trace["function"] : "", isset($trace["class"]) ? $trace["class"] : "", isset($trace["args"]) ? $trace["args"] : "");
                    }
                }

                echo "</div>";
                if (!self::$footer) {
                    self::$footer = true;
                    self::echo_server_usage();
                }
                exit;
            } else {
                if (!self::$header) {
                    self::$header = true;
                    echo "<style type='text/css'>" . file_get_contents(dirname(__FILE__) . DS . "debug.css") . "</style>";
                    echo "<script type='text/javascript'>" . file_get_contents(dirname(__FILE__) . DS . "debug.js") . "</script>";
                }
                if (!self::$footer) {
                    self::$footer = true;
                    self::echo_server_usage();
                }
            }
        } else {
            echo "<h1>Sorry, here is error :(</h1>";
            exit;
        }
        return true;
    }

    private static function echo_stack($opened, $message, $file = "", $line = 0, $function = "", $class = "", $args = "")
    {
        if (IS_AJAX) return;
        echo "<div class='debugger_stack " . ($opened ? "debugger_stack_opened" : "") . "'>";
        echo "<h3>" . $message . "</h3>";
        if ($file != "") {
            if (file_exists($file)) {
                $data = file($file);
                if ($line <= count($data)) {
                    echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>" . $file . " (line " . $line . ")</h4></div><div class='debugger_file_content'>";
                    foreach ($data as $c => $l) {
                        if ($c < ($line + 10) && $c > ($line - 12))
                            echo "<span class='debugger_line" . (($c + 1) == $line ? " debugger_selected" : "") . "'>" . ($c + 1) . ": " . $l . "</span>";
                    }
                    echo "</div></div>";
                }
            }
        }
        echo "</div>";
    }

    private static function  echo_server_usage()
    {
        if (IS_AJAX) return;
        echo "<div class='debugger_server'><div class='debugger_content'>";

        echo "<div class='debugger_block'><div class='debugger_block_name' onclick='javascript:changeClass(this.parentNode,\"debugger_opened\")'>GLOBAL (" . self::Memory_Usage() . " MB)</div>";
        echo "<div class='debugger_block_content'><h2>GLOBAL vars</h2><pre>";

        echo "<div class='debugger_stack'>";
        echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>RAM & Processor usage</h4></div><div class='debugger_file_content'>";
        echo "Memory usage (RAM): ";
        echo self::Memory_Usage();
        echo " MB<br>";
        echo "Memory limit (RAM): ";
        echo ini_get("memory_limit");
        echo "B<br>";
        echo "Page generation time (Sec.): " . (microtime(true) - APP_START) . "<br>";
        echo "</div></div>";
        echo "</div>";

        if (isset($_SESSION["redirect_log"])) {
            foreach ($_SESSION["redirect_log"] as $k => $v) {
                echo "<div class='debugger_stack'>";
                echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>#$k HTTP Redirect ($v[from_path] -> $v[to_path], method " . $v["SERVER"]["REQUEST_METHOD"] . ")</h4></div><div class='debugger_file_content'>";
                echo self::dump_to_table($v);
                echo "</div></div>";
                echo "</div>";
            }
            $_SESSION["redirect_log"] = array();
        }

        echo "<div class='debugger_stack'>";
        echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>\$_SERVER vars</h4></div><div class='debugger_file_content'>";
        echo self::dump_to_table($_SERVER);
        echo "</div></div>";
        echo "</div>";

        echo "<div class='debugger_stack'>";
        echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>\$_SESSION vars</h4></div><div class='debugger_file_content'>";

        if (isset($_SESSION)) echo self::dump_to_table($_SESSION);
        else echo "\$_SESSION not started";
        echo "</div></div>";
        echo "</div>";

        echo "<div class='debugger_stack'>";
        echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>\$_COOKIE vars</h4></div><div class='debugger_file_content'>";
        echo self::dump_to_table($_COOKIE);
        echo "</div></div>";
        echo "</div>";

        echo "</pre></div>";
        echo "</div>";

        if (count(self::$mysql) > 0) {
            echo "<div class='debugger_block'><div class='debugger_block_name' onclick='javascript:changeClass(this.parentNode,\"debugger_opened\")'>MySQL (" . count(self::$mysql) . ")</div>";
            echo "<div class='debugger_block_content'><h2>MySQL Queries (count " . count(self::$mysql) . ")</h2><pre>";
            foreach (self::$mysql as $m) {
                echo "<div class='debugger_stack'>";
                echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>Execution $m[time] seconds, " . count($m["result"]) . " results<br>$m[query]</h4></div><div class='debugger_file_content'>";
                echo self::dump_to_table($m["result"]);
                echo "</div></div>";
                echo "</div>";
            }
            echo "</pre></div>";
            echo "</div>";
        }
        if (count(self::$dumps) > 0) {
            echo "<div class='debugger_block'><div class='debugger_block_name' onclick='javascript:changeClass(this.parentNode,\"debugger_opened\")'>VAR_DUMPS (" . count(self::$dumps) . ")</div>";
            echo "<div class='debugger_block_content'><h2>VAR_DUMPS (count " . count(self::$dumps) . ")</h2><pre>";
            foreach (self::$dumps as $k => $m) {
                echo "<div class='debugger_stack'>";
                echo "<div class='debugger_file'><div class='debugger_file_header' onclick='javascript:changeClass(this.parentNode.parentNode,\"debugger_stack_opened\")'><h4>$m[name]</h4></div><div class='debugger_file_content'>";
                echo self::dump_to_table($m["var"]);
                echo "</div></div>";
                echo "</div>";
            }
            echo "</pre></div>";
            echo "</div>";
        }
        if (count(self::$detailed) > 0) {
            echo "<div class='debugger_block'><div class='debugger_block_name' onclick='javascript:changeClass(this.parentNode,\"debugger_opened\")'>WARNINGS (" . count(self::$detailed) . ")</div>";
            echo "<div class='debugger_block_content'><h2>WARNINGS LOG (count " . count(self::$detailed) . ")</h2>";
            echo "<pre>";
            foreach (self::$detailed as $det) {
                self::echo_stack(false, self::FriendlyErrorType($det["errno"]) . ": " . $det["errstr"], $det["file"], $det["line"]);
            }
            echo "</pre>";
            echo "</div>";
            echo "</div>";
        }
        echo "<div class='debugger_block' id='debugger_ajax'><div class='debugger_block_name' onclick='javascript:changeClass(this.parentNode,\"debugger_opened\")'>AJAX (<span id='debugger_ajax_count'>0</span>)</div>";
        echo "<div class='debugger_block_content'><h2>AJAX Requests (count <span id='debugger_ajax_count2'>0</span>)</h2><div id='debugger_ajax_content'><pre></pre></div>";

        echo "</div>";
        echo "</div>";

        echo "</div></div>";
    }

    private static function Memory_Usage($decimals = 2)
    {
        $result = 0;

        if (function_exists('memory_get_usage')) {
            $result = memory_get_usage() / 1024;
        } else {
            if (function_exists('exec')) {
                $output = array();

                if (substr(strtoupper(PHP_OS), 0, 3) == 'WIN') {
                    exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

                    $result = preg_replace('/[\D]/', '', $output[5]);
                } else {
                    exec('ps -eo%mem,rss,pid | grep ' . getmypid(), $output);

                    $output = explode('  ', $output[0]);

                    $result = $output[1];
                }
            }
        }

        return number_format(intval($result) / 1024, $decimals, '.', '');
    }

    private static function FriendlyErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return "";
    }

    private static function dump_to_table($data, $level = 0)
    {
        $retval = '<table class="debug_table ' . ($level > 2 ? "collapese" : "") . '" onclick="changeClass(this.parentNode.parentNode.parentNode,\'collapese\')">';
        if (is_numeric($data)) $retval .= "Number: $data";
        elseif (is_string($data)) $retval .= "String: '$data'";
        elseif (is_null($data)) $retval .= "NULL";
        elseif ($data === true) $retval .= "TRUE";
        elseif ($data === false) $retval .= "FALSE";
        elseif (is_array($data)) {
            $retval .= "<thead><tr><th colspan='2'>Array (" . count($data) . ")</th></tr></thead><tbody>";
            foreach ($data AS $key => $value) {
                $retval .= "<tr><td><strong>$key</strong></td><td>";
                $retval .= self::dump_to_table($value, $level + 1) . "</td></tr>";
            }
            $retval .= "</tbody>";
        } elseif (is_object($data)) {
            $retval .= "<thead><tr><th colspan='2'>Object (" . get_class($data) . ")</th></tr></thead><tbody>";
            foreach (get_class_methods($data) AS $key => $value) {
                $retval .= "<tr><td><strong>Method ($key)</strong></td><td>";
                $retval .= "Method ($value)</td></tr>";
            }
        }
        return $retval . "</table>";
    }
}
