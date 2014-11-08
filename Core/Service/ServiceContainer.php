<?php
/**************************************************************
 *
 * ServiceContainer.php, created 3.11.14
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
namespace PKRS\Core\Service;

class ServiceContainer extends \PKRS\Core\Service\AppService
{

    private $services = array();
    private $objects = array();
    private $config;
    static $self;

    /**
     * @param string $config_ini_file (path to application config ini file)
     */
    public function __construct($config_ini_file)
    {
        parent::set_container($this);
        \PKRS\Core\Service\Service::set_containr($this);
        $this->config = new \PKRS\Core\Config\Config($config_ini_file);
        $this->js_css_consolidate_service();
        $this->services = $this->config->getServices();
        $this->objects["config"] = $this->config;
        $this->objects["service_container"] = $this;
        self::$self = $this;
        // first - We need start hooks!!!
        $hooks = $this->get_hooks();
        $hooks->register_action("presenters", "on_create", array("\\PKRS\\Core\\Modules\\PHPModule", "run_modules"), array($this, (array)@$this->config->get("modules")), 0);
        $hooks->register_action("modules", "on_create", array("\\PKRS\\Core\\Modules\\PHPModule", "check_instance"), array($this, (array)@$this->config->get("modules")), 0);
        $hooks->execute("service", "on_create");
        $hooks->execute("application", "on_start");
        // init debuger
        $debug = $this->get_debug();
    }

    public static function get_instatce()
    {
        return self::$self;
    }

    /**
     * @param $name
     * @param string $prefix
     * @return \PKRS\Core\Modules\PHPModule
     */
    public function get_php_module($name, $prefix = "")
    {
        return \PKRS\Core\Modules\PHPModule::get_module($name, $prefix);
    }

    /**
     * @return \PKRS\Core\Service\ServiceContainer
     */
    public function get_service_container()
    {
        return $this;
    }

    /**
     * @return \PKRS\Core\Hooks\Hooks
     */
    public function get_hooks()
    {
        return $this->get_service("hooks");
    }

    /**
     * @return \PKRS\Core\Config\Config
     */
    public function get_config()
    {
        return $this->get_service("config");
    }

    /**
     * @return \PKRS\Core\Debug\Debug
     */
    public function get_debug()
    {
        return $this->get_service("debug");
    }

    /**
     * @return \PKRS\Core\Config\DbConfig
     */
    public function get_db_config()
    {
        return $this->get_service("db_config");
    }

    /**
     * @return \PKRS\Core\Mailer\Mailer
     */
    public function get_mailer()
    {
        return $this->get_service("mailer");
    }

    /**
     * @return \PKRS\Core\Database\Database
     */
    public function get_database()
    {
        return $this->get_service("database");
    }

    /**
     * @return \PKRS\Core\Router\Router
     */
    public function get_router()
    {
        return $this->get_service("router");
    }

    /**
     * @return \PKRS\Core\Session\Session
     */
    public function get_session()
    {
        return $this->get_service("session");
    }

    /**
     * @return \PKRS\Core\Session\Cookies
     */
    public function get_cookies()
    {
        return $this->get_service("cookies");
    }

    /**
     * @return \PKRS\Core\Requests\Form
     */
    public function get_forms()
    {
        return $this->get_service("forms");
    }

    /**
     * @return \PKRS\Core\User\User
     */
    public function get_user()
    {
        return $this->get_service("user");
    }

    /**
     * @return \PKRS\Core\User\Messages
     */
    public function get_messages()
    {
        return $this->get_service("messages");
    }

    /**
     * @return \PKRS\Core\View\Smarty
     */
    public function get_view()
    {
        return $this->get_service("view");
    }

    /**
     * @return \PKRS\Helpers\Transform\Arrays
     */
    public function get_transformArrays()
    {
        return $this->get_service("transform_arrays");
    }

    /**
     * @return \PKRS\Helpers\Transform\DateTime
     */
    public function get_transformDateTime()
    {
        return $this->get_service("transform_datetime");
    }

    /**
     * @return \PKRS\Helpers\Transform\Numbers
     */
    public function get_transformNumbers()
    {
        return $this->get_service("transform_numbers");
    }

    /**
     * @return \PKRS\Helpers\Transform\Strings
     */
    public function get_transformStrings()
    {
        return $this->get_service("transform_strings");
    }

    /**
     * @return \PKRS\Helpers\Validator\Validator
     */
    public function get_validator()
    {
        return $this->get_service("validator");
    }

    private function get_service($type)
    {
        $type = strtolower($type);
        if (isset($this->objects[$type]))
            return $this->objects[$type];
        else return $this->create_service($type);
    }

    private function get_service_name_by_class($class)
    {
        foreach ($this->services as $name => $data) {
            if ($data["class"] == $class) {
                return $name;
            }
        }
        throw new \Exception("Get service by class: $class not defined");
    }

    /**
     * @param string $name - Service name
     * @return object
     * @throws \PKRS\Core\Exception\ServiceException
     */
    private function create_service($name)
    {
        $name = strtolower($name); // fix - service name only small letters
        if (!in_array($name, array_keys($this->services))) { // is service defined?
            throw new \PKRS\Core\Exception\ServiceException("Service $name not defined");
        } else {
            // yes, service defined
            if (in_array($name, array_keys($this->objects))) {
                // we have object - return it
                return $this->objects[$name];
            } else {
                // object not created - create it
                $service = $this->services[$name];
                $class = "\\" . str_replace("/", "\\", trim($service["class"], "/"));
                $params = array();
                foreach ($service["params"] as $param) {
                    if ($param["type"] == "service")
                        $object = $this->get_service($this->get_service_name_by_class($param["value"]));
                    elseif ($param["type"] == "extern") {
                        $cl = "\\" . str_replace("/", "\\", trim($param["value"], "/"));
                        $object = new $cl();
                    } elseif ($param["type"] == "this")
                        $object = $this;
                    else {
                        $object = $param["value"];
                    }
                    $params[] = $object;
                }
                $reflection = new \ReflectionClass($class);
                if (!$reflection->isSubclassOf("\\PKRS\\Core\\Service\\Service")) {
                    throw new \PKRS\Core\Exception\ServiceException("Service $name is not instance of \\PKRS\\Core\\Service\\Service()");
                }
                if ($reflection->hasMethod("__construct"))
                    $object = $reflection->newInstanceArgs($params);
                else $object = $reflection->newInstance();
                // register service object
                $this->objects[$name] = $object;
                // return new object

                return $this->objects[$name];
            }
        }
    }

    private function js_css_consolidate_service()
    {
        $cache = $this->config->get("application")["consolidate_scripts_caching"] == "true";
        if (isset($_GET["__files_"])) {
            if (isset($_GET["__load_consolidated_"]) && $_GET["__load_consolidated_"] == "js") {
                header("Content-type:text/javascript");
                if ($cache && file_exists(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".js")) {
                    echo file_get_contents(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".js");
                    exit;
                }
                $ech = "";
                foreach (explode(",", $_GET["__files_"]) as $f) {
                    if (substr($f, 0, 2) == "//")
                        $ech .= file_get_contents("http:" . $f);
                    else if (substr($f, 0, 1) == "/")
                        $ech .= file_get_contents("http://" . $_SERVER["HTTP_HOST"] . "/" . $f);
                    else $ech .= file_get_contents($f);
                }
                if ($cache) {
                    $h = fopen(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".js", "w+");
                    fwrite($h, $ech);
                    fclose($h);
                }
                echo $ech;
                exit;
            }
            if (isset($_GET["__load_consolidated_"]) && $_GET["__load_consolidated_"] == "css") {
                header("Content-type:text/css");
                if ($cache && file_exists(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".css")) {
                    echo file_get_contents(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".css");
                    exit;
                }
                $ech = "";
                foreach (explode(",", $_GET["__files_"]) as $f) {
                    if (substr($f, 0, 2) == "//")
                        $ech .= file_get_contents("http:" . $f);
                    else if (substr($f, 0, 1) == "/")
                        $ech .= file_get_contents("http://" . $_SERVER["HTTP_HOST"] . "/" . $f);
                    else $ech .= file_get_contents($f);
                }
                if ($cache) {
                    $h = fopen(ROOT_DIR . $this->config->get("application")["cache_dir"] . sha1($_GET["__files_"]) . ".js", "w+");
                    fwrite($h, $ech);
                    fclose($h);
                }
                echo $ech;
                exit;
            }
        }
    }
}