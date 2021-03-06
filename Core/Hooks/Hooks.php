<?php
/**************************************************************
 *
 * Hooks.php, created 5.11.14
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
namespace PKRS\Core\Hooks;

use PKRS\Core\Exception\ServiceException;
use PKRS\Core\Service\Service;

class Hooks extends Service
{

    private $service;
    private $hooks = array();

    public function __construct()
    {
        $this->service = self::gc();
        if (file_exists(dirname(__FILE__) . DS . "allowedHooks.neon")) {
            $hooks = $this->service->get_config()->parse_config(dirname(__FILE__) . DS . "allowedHooks.neon");
            foreach ($hooks as $section => $value)
                foreach ($value as $action => $val) {
                    $this->register_hook($section, $action);
                }
        }
    }

    public function register_hook($section, $action)
    {
        if (isset($this->hooks[$section][$action])) {
            return false;
        } else {
            if (!isset($this->hooks[$section])) $this->hooks[$section] = array();
            $this->hooks[$section][$action] = array(
                0 => array(),
                1 => array(),
                2 => array(),
                3 => array(),
                4 => array(),
                5 => array(),
                6 => array(),
                7 => array(),
                8 => array(),
                9 => array(),
                10 => array(),
            );
            return true;
        }
    }

    /**
     * @param $section
     * @param $action
     * @param $callable
     * @param array $params
     * @param int $level
     * @return array
     * @throws ServiceException
     */
    public function register_action($section, $action, $callable, $params = array(), $level = 5)
    {
        if (!is_callable($callable)) throw new ServiceException("Register hook: " . var_export($callable, true) . " is not callable!");
        $level = self::gc()->get_transformNumbers()->limit_between($level, 0, 10);
        if (!isset($this->hooks[$section][$action])) $this->register_hook($section, $action);
        if (!isset($this->hooks[$section][$action][$level])) $this->hooks[$section][$action][$level] = array();
        return $this->hooks[$section][$action][$level][] = array("callable" => $callable, "params" => $params);
    }

    /**
     * @param $section
     * @param $action
     * @param array $input
     */
    public function execute($section, $action, $input = array())
    {
        $this->_execute("all", "before_hook_execute", func_get_args());
        $this->_execute($section, $action, $input);
        $this->_execute("all", "after_hook_execute", func_get_args());
    }

    /**
     * @param $section
     * @param $action
     * @param array $input
     */
    private function _execute($section, $action, $input = array())
    {
        if (isset($this->hooks[$section][$action])) {
            foreach ($this->hooks[$section][$action] as $hooks) {
                foreach ($hooks as $hook) {
                    if (!!$input) {
                        if (is_array($hook["params"]))
                            $hook["params"]["_input"] = $input;
                        else $hook["params"] = array("param" => $hook["params"], "_input" => $input);
                    }
                    if (is_array($hook["params"]))
                        call_user_func_array($hook["callable"], $hook["params"]);
                    else call_user_func($hook["callable"], $hook["params"]);
                }
            }
        }
    }
}