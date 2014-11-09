<?php
/********************************************
 *
 * Config.php, created 5.8.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 *
 ***************************************************************
 *
 * Contacts:
 * Core developer - djpitrrs@gmail.com
 * Website        - www.pkrs.eu
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS\Core\Config;

class Config extends \PKRS\Core\Service\Service
{

    private $allowed_types = array(
        "object",
        "string",
        "int",
        "array",
        "file",
        "this",
        "extern",
        "service"
    );

    private $config = array();
    private $loaded = array();
    private $services = array();

    public function __construct($config_file=null)
    {
        $this->parseDefault();
        if ($config_file !== null)
            $this->load_config($config_file);
    }

    public function parse_config($config_file)
    {
        if (in_array(sha1($config_file),array_keys($this->loaded)))
            return (array)$this->loaded[sha1($config_file)];
        if (file_exists($config_file)) {
            if (substr($config_file, strlen($config_file) - 5, strlen($config_file)) == ".neon") {
                $neon = new \Neon();
                $conf = $neon->decode($config_file);
            }
            else if (substr($config_file, strlen($config_file) - 4, strlen($config_file)) == ".php"){
                $file = include($config_file);
                if (is_array($file))
                    $conf = $file;
                else throw new \PKRS\Core\Exception\ConfigException("Config: returned value from PHP config is not array!");
            }
            else {
                throw new \PKRS\Core\Exception\ConfigException("Config: file extension must be only .php or .neon");
            }
            $this->loaded[sha1($config_file)] = $conf;
            return $conf;
        } else {
            throw new \PKRS\Core\Exception\FileException("Config: file $config_file not exists!");
        }
    }

    public function load_config($config_file)
    {
        $conf = $this->parse_config($config_file);
        foreach ($conf as $section => $value) {
            foreach ($value as $key => $val)
                $this->config[$section][$key] = $val;
        }
        $this->parseServices();
    }

    public function force_load($config_file)
    {
        if (file_exists($config_file)) {
            $this->load_config($config_file);
            $this->parseServices();
        }
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function getAll()
    {
        return $this->config;
    }

    private function parseDefault()
    {
        if (file_exists(dirname(__FILE__) . DS . "defaultConfig.neon")) {
            $conf = $this->parse_config(dirname(__FILE__).DS."defaultConfig.neon");
            foreach ($conf as $k => $v) {
                $this->set($k, $v);
            }
        }
        if (file_exists(dirname(__FILE__) . DS . "defaultServices.neon")) {
            $conf = $this->parse_config(dirname(__FILE__) . DS . "defaultServices.neon");
            foreach ($conf as $name => $settings) {
                if (isset($settings["class"])) {
                    $cl_name = "\\" . str_replace("/", "\\", trim($settings["class"], "/")); // fix config escaping
                    if (class_exists($cl_name,true)) {
                        $cache = array("class" => $settings["class"], "name" => $name, "params" => array());
                        unset($settings["class"]);
                        if (isset($settings["params"]) && is_array($settings["params"])){
                            foreach ($settings["params"] as $type => $dependency) {
                                if (is_array($dependency)){
                                    $c = array();
                                    foreach($dependency as $k=>$v){
                                        $c[] = $this->fix_dependency($conf, $name, $type, $v);
                                    }
                                    $dependency = $c;
                                    unset($c);
                                } else {
                                     $dependency = array($this->fix_dependency($conf, $name, $type, $dependency));
                                }
                                $cache["params"][] = array("type"=>$type, "dependency"=>$dependency);
                                echo "service $name - $type -> ".var_export($dependency,true)."<br>";
                            }
                        }
                        $this->services[strtolower($name)] = $cache;
                    } else throw new \PKRS\Core\Exception\ConfigException("Service class $cl_name not exists!");
                } else throw new \PKRS\Core\Exception\ConfigException("File defaultServices.ini corrupted! Not defined class in $name service!");
            }
        }
    }

    /**
     * @param array $all_config
     * @param string $service_name
     * @param string $type
     * @param string $dependency
     * @return string
     * @throws \PKRS\Core\Exception\FileException
     */
    private function fix_dependency($all_config, $service_name, $type, $dependency){
        if ($type == "file"){
            // file must be relative to APP_DIR
            if (!file_exists(APP_DIR.$dependency)){
                throw new \PKRS\Core\Exception\FileException("Service $service_name loading: file $dependency as param not exists!");
            }
        } else if ($type == "service"){
            if (!in_array($dependency, array_keys($all_config))){
                throw new \PKRS\Core\Exception\FileException("Service $service_name loading: service $dependency not defined!");
            }
        } else if ($type == "class"){
            $dependency =  "\\" . str_replace("/", "\\", trim($dependency, "/"));
            if (!class_exists($dependency, true)){
                throw new \PKRS\Core\Exception\FileException("Service $service_name loading: dependency class $dependency not exists!");
            }
        } else {
            throw new \PKRS\Core\Exception\FileException("Services loading: service type $type not allowed (only file, service, class)!");
        }
        return $dependency;
    }

    private function parseServices()
    {
        if (!isset($this->config["services_overide"]) || !is_array($this->config["services_overide"])) return;
        foreach ($this->config["services_overide"] as $key => $value) {
            if (isset($value["class"])) {
                $cl_name = "\\" . str_replace(array("/","\\"), "\\", trim($value["class"], "/"));
                if (class_exists($cl_name, true)) {
                    $cache = array("class" => $value["class"], "name" => $key, "params" => array());
                    foreach ($value as $param => $dependency) {
                        if ($param == "class") continue;
                        if (substr($param, 0, strlen("param")) == "param") {
                            $parts = explode("_", $param);
                            if (isset($parts[1])) {
                                if (isset($parts[2]) && !in_array($parts[2], $this->allowed_types)) {
                                    throw new \PKRS\Core\Exception\ConfigException("Parsing config: Type of param service $key -> $dependency ($parts[2]) is not allowed!");
                                }
                                $cache["params"][] = array(
                                    "type" => isset($parts[2]) ? $parts[2] : "object",
                                    "value" => $dependency
                                );
                            }
                        }
                    }
                    $this->services[strtolower($key)] = $cache;
                } else throw new \PKRS\Core\Exception\ConfigException("Service class $cl_name not exists!");
            } else {
                throw new \PKRS\Core\Exception\ConfigException("Service $key has no defined Class!");
            }
        }
    }

    public function get($key, $default = "")
    {
        if (in_array($key, array_keys($this->config))) {
            return $this->config[$key];
        } else {
            // back compatibility
            if (in_array($key, array_keys($this->config["application"])))
                return $this->config["application"][$key];
            $this->config[$key] = $default;
            return $default;
        }
    }

    public function getServices()
    {
        return $this->services;
    }

}