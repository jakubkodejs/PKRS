<?php
/**************************************************************
 *
 * PHPModule.php, created 5.11.14
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
namespace PKRS\Core\Modules;

abstract class PHPModule extends BaseModule implements IPHPModule
{

    private static $serviceContainer;
    var $db_prefix;
    private static $loaded = array();

    final public function __construct(\PKRS\Core\Service\ServiceContainer $serviceContainer, $db_prefix = "")
    {
        parent::__construct();
        $this->db_prefix = $db_prefix;
        self::$serviceContainer = $serviceContainer;
        self::$serviceContainer->get_hooks()->execute("modules", "on_create");
        $this->start();
    }

    final static function gc()
    {
        return self::$serviceContainer;
    }

    final static function get_module($name, $prefix = "")
    {
        if (isset(self::$loaded[$name . ($prefix != "" ? "-$prefix" : "")])) {
            return self::$loaded[$name . ($prefix != "" ? "-$prefix" : "")];
        } else {
            throw new \PKRS\Core\Exception\ServiceException("Get module: Module " . $name . ($prefix != "" ? "-$prefix" : "") . " not loaded and configured!");
        }
    }

    final static function check_instance()
    {

    }

    final static function run_modules(\PKRS\Core\Service\ServiceContainer $serviceContainer, $config_modules = array())
    {
        foreach ($config_modules as $name => $actions) {
            if (is_array($actions)) {
                foreach ($actions as $pref => $data) {
                    if (in_array($name, array_keys(self::$loaded))) continue;
                    $data = explode(",", $data);
                    $cl_name = "\\Modules\\PHPModules\\$name\\$name";
                    if (!class_exists($cl_name, true)) throw new \PKRS\Core\Exception\ServiceException("Module $name not exists");
                    $object = new $cl_name($serviceContainer, $pref);
                    if (!is_subclass_of($object, "\\PKRS\\Core\\Modules\\PHPModule")) throw new \PKRS\Core\Exception\ServiceException("Module $name is not instance of \\PKRS\\Core\\Modules\\PHPModule()");
                    foreach ($data as $action) {
                        if ($action == "start") continue;
                        if (method_exists($object, $action)) {
                            $res = $object->$action($pref);
                            if ($action == "_install") {
                                if ($res) self::gc()->get_messages("Module $name installed");
                                else self::gc()->get_messages("Module $name not installed", "err");
                            }
                            if ($action == "_remove") {
                                if ($res) self::gc()->get_messages("Module $name removed");
                                else self::gc()->get_messages("Module $name not removed", "err");
                            }
                        } else throw new \PKRS\Core\Exception\ConfigException("Modules: action $action not exists on $name-$pref!");
                    }
                    self::$loaded[$name . "-" . $pref] = $object;
                }
            } else {
                if (in_array($name, array_keys(self::$loaded))) continue;
                $actions = explode(",", $actions);
                $cl_name = "\\Modules\\PHPModules\\$name\\$name";
                if (!class_exists($cl_name, true)) throw new \PKRS\Core\Exception\ServiceException("Module $name not exists");
                $object = new $cl_name($serviceContainer);
                if (!is_subclass_of($object, "\\PKRS\\Core\\Modules\\PHPModule")) throw new \PKRS\Core\Exception\ServiceException("Module $name is not instance of \\PKRS\\Core\\Modules\\PHPModule()");
                foreach ($actions as $action) {
                    if ($action == "start") continue;
                    if (method_exists($object, $action)) $object->$action();
                    else throw new \PKRS\Core\Exception\ConfigException("Modules: action $action not exists on $name!");
                }
                self::$loaded[$name] = $object;
            }
        }
    }

}