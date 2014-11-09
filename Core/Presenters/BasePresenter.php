<?php
/********************************************
 *
 * BasePresenter.php, created 5.8.14
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
namespace PKRS\Core\Presenters;

use Exception;
use PKRS\Core\Exception\FileException;
use PKRS\Core\Exception\MainException;
use PKRS\Core\Headers\Redirects;
use PKRS\Core\Service\AppService;
use PKRS\Core\User\Messages;
use Smarty_Internal_Template;

abstract class BasePresenter extends AppService implements IBasePresenter
{
    var $smarty;
    var $template;
    var $theme;
    var $need_login = false;
    var $config;
    var $page_title = "";
    var $js_modules = array();
    protected $header_js = array();
    protected $header_css = array();
    protected $user = null;
    protected $now_logged = false;
    protected $forms = array();


    public function __construct()
    {
        self::gc()->get_hooks()->execute("presenters", "on_create");
        $this->smarty = self::get_container()->get_view()->smarty();
        $this->config = self::get_container()->get_config();
        self::register_app();
        $this->need_login();
        if ($this->need_login) {
            $this->user = self::get_container()->get_user();
            if (method_exists($this->user, "check_login")) {
                $this->now_logged = $this->user->check_login();
                if ($this->user->logged)
                    $this->smarty->assign("user", $this->user->get_row());
                else {
                    if (defined("IS_AJAX") && IS_AJAX == true) {
                        echo "LOGIN_ERR";
                        exit;
                    }
                    Redirects::redirect($this->config->get("login_redirect", "/login"));
                }
            } else throw new \PKRS\Core\Exception\MainException();
        }
        $this->js_modules = $this->get_available_js_modules();
    }

    final function private__post_construct()
    {
        $this->set_theme();
        $this->after_set_theme();
        $this->post_construct();
        if (!empty($_POST)) {
            $action = isset($_POST["action"]) ? $_POST["action"] : null;
            $this->process_post($action);
        }
    }

    private function after_set_theme()
    {
        if (!defined("JS_PATH")) define("JS_PATH", THEMES_DIR . $this->theme . DS . "js");
        if (!defined("CSS_PATH")) define("CSS_PATH", THEMES_DIR . $this->theme . DS . "css");
    }

    public function add_js($js_file)
    {
        if (substr($js_file, 0, 4) == "http" || substr($js_file, 0, 1) == "/") {
            if (!in_array($js_file, $this->header_js))
                $this->header_js[] = $js_file;
        } else if (file_exists(ROOT_DIR . JS_PATH . $js_file)) {
            if (!in_array(JS_PATH . $js_file, $this->header_js))
                $this->header_js[] = JS_PATH . $js_file;
        } else throw new Exception("JS File $js_file not exists in path " . JS_PATH);
    }

    public function add_css($css_file)
    {
        if (substr($css_file, 0, 4) == "http" || substr($css_file, 0, 1) == "/") {
            if (!in_array($css_file, $this->header_css))
                $this->header_css[] = $css_file;
        } else if (file_exists(ROOT_DIR . CSS_PATH . $css_file)) {
            if (!in_array(CSS_PATH . $css_file, $this->header_css))
                $this->header_css[] = CSS_PATH . $css_file;
        } else throw new Exception("CSS File $css_file not exists in path " . CSS_PATH);

    }

    final public function page_title($title)
    {
        $this->page_title = $title;
    }

    final public function add_js_module($module_name)
    {
        if (in_array($module_name, array_keys($this->js_modules))) {
            foreach ($this->js_modules[$module_name]["css"] as $css) {
                $this->add_css($css);
            }
            foreach ($this->js_modules[$module_name]["js"] as $js) {
                $this->add_js($js);
            }
        } else {
            throw new MainException("JS Module $module_name not exists!");
        }
    }

    final public function display()
    {
        $this->smarty->assign("page_title", $this->page_title);
        if (self::gc()->get_config()->get("application")["consolidate_scripts"] == "true") {
            $this->header_css = array("/?__load_consolidated_=css&__files_=" . implode(",", $this->header_css));
            $this->header_js = array("/?__load_consolidated_=js&__files_=" . implode(",", $this->header_js));
        }
        $this->smarty->assign("header_css", $this->header_css);
        $this->smarty->assign("header_js", $this->header_js);
        $this->smarty->setTemplateDir(THEMES_DIR . $this->theme);
        $this->smarty->assign("TPL_PATH", THEMES_PATH . $this->theme . "/");
        //$this->smarty->registerFilter("output", array($this,"minify_html"));
        $this->smarty->assign("messages", Messages::get_all_imploded());
        if (!is_file(THEMES_DIR . $this->theme . DS . $this->template) || !file_exists(THEMES_DIR . $this->theme . DS . $this->template)) {
            throw new FileException(__CLASS__ . ": Template " . THEMES_DIR . $this->theme . DS . $this->template . " not exists!");
        } else $this->smarty->display(THEMES_DIR . $this->theme . DS . $this->template);
    }

    final public function get_available_js_modules()
    {
        $modules = array();
        $search_dir = APP_DIR . "Modules" . DS . "JSModules" . DS;
        foreach (glob($search_dir . "*", GLOB_ONLYDIR) as $dir) {
            $dir = str_replace($search_dir, '', $dir);
            if (file_exists($search_dir . $dir . DS . "_load.csv")) {
                $load = file($search_dir . $dir . DS . "_load.csv");
                if (!empty($load)) {
                    $add = array("css" => array(), "js" => array());
                    foreach ($load as $l) {
                        $c = explode(";", $l);
                        if (is_array($c) && isset($c[1]) && in_array($c[0], array_keys($add))) {
                            $add[$c[0]][] = JS_MODULES_PATH . $dir . "/" . $c[1];
                        }
                    }
                    $modules[$dir] = $add;
                }
            }
        }
        return $modules;
    }

    function process_post($action){

    }

    function post_construct(){

    }

    function before_display(){

    }


    // Minifi HTML output
    final public static function minify_html($tpl_output, Smarty_Internal_Template $template)
    {
        $tpl_output = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $tpl_output);
        return $tpl_output;
    }

    // Alias for redirecting
    final function redirect($path)
    {
        Redirects::redirect($path);
    }

}