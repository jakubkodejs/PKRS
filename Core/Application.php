<?php
/********************************************
 *
 * Application.php, created 5.8.14
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
namespace PKRS\Core;

use PKRS\Core\Exception\MainException;

class Application
{

    private $config;
    private $router;
    private $serviceContainer;

    public function __construct(\PKRS\Core\Service\ServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        $this->config = $serviceContainer->get_config();
        $this->router = $serviceContainer->get_router();
        $serviceContainer->get_hooks()->execute("application", "on_create");

    }

    public function run()
    {
        try {
            $route = $this->router->match();
            if (!$route) {
                if (defined("DEV") && DEV) {
                    throw new MainException("Aplication: Route " . trim($_SERVER["REQUEST_URI"], "/") . " (method $_SERVER[REQUEST_METHOD]) not exists");
                }
                \PKRS\Core\Headers\Redirects::e404($this->config->get("base_path"), $this->config->get("page_404", "404"));
            } else {
                $class_name = "\\Presenters\\" . $route["target"]["ns"] . "\\" . $route["target"]["c"];
                $method_name = $route["target"]["a"];
                if (class_exists($class_name)) {
                    $c = new $class_name();
                    if (is_subclass_of($c, "\\PKRS\\Core\\Presenters\\BasePresenter")) {
                        if (method_exists($c, $method_name)) {
                            $c->private__post_construct();
                            $c->post_construct();
                            $c->$method_name($route["params"]);
                            $c->before_display();
                            $c->display();
                            \PKRS\Core\User\Auth::deinit();
                        } else {
                            if (defined("DEV") && DEV) {
                                throw new \PKRS\Core\Exception\FileException("Application: Method $class_name->$method_name(\$params) not exists");
                            }
                            \PKRS\Core\Headers\Redirects::e404($this->config->get("base_path"), $this->config->get("page_404", "404"));
                        }
                    } else {
                        if (defined("DEV") && DEV) {
                            throw new \PKRS\Core\Exception\FileException("Application: Class $class_name is not instance of \\PKRS\\Controller\\Controller()");
                        }
                        \PKRS\Core\Headers\Redirects::e404($this->config->get("base_path"), $this->config->get("page_404", "404"));
                    }
                } else {

                    if (defined("DEV") && DEV) {
                        throw new \PKRS\Core\Exception\FileException("Application: Class $class_name not exists");
                    }
                    \PKRS\Core\Headers\Redirects::e404($this->config->get("base_path"), $this->config->get("page_404", "404"));
                }
            }
        } catch (\Exception $e) {
            throw new \PKRS\Core\Exception\MainException($e->getMessage(), $e->getCode(), $e);
        }
        $this->serviceContainer->get_hooks()->execute("application", "on_exit");
    }

}