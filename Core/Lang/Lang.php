<?php
/********************************************
 *
 * Lang.php, created 8.8.14
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
namespace PKRS\Core\Lang;

class Lang
{

    private static $loaded = false;

    private static $lang = array();

    public static function get_allowed_langs()
    {
        $allowed = array();
        foreach (new \DirectoryIterator(APP_DIR . DS . 'Lang') as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if (substr($fileInfo->getFilename(), strlen($fileInfo->getFilename()) - 4, 4) == ".ini")
                $allowed[] = substr($fileInfo->getFilename(), 0, strlen($fileInfo->getFilename() - 4));
        }
        return $allowed;
    }

    public static function get_all_lang()
    {
        return self::$lang;
    }

    public static function detect_lang($force = null)
    {
        if ($force !== null && in_array($force, self::get_allowed_langs())) {
            $_SESSION["lang"] = $force;
            return $force;
        }
        $allowed = self::get_allowed_langs();
        if (isset($_GET["lang"])) {
            if (in_array($_GET["lang"], $allowed)) {
                $_SESSION["lang"] = $_GET["lang"];
            } else {
                \PKRS\Core\Service\ServiceContainer::get_instatce()->get_messages()->set("Lang not exists!");
            }
        }
        if (isset($_SESSION["lang"]) && in_array($_SESSION["lang"], $allowed)) {
            return $_SESSION["lang"];
        }
        $langs = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
            if (count($lang_parse[1])) {
                $langs = array_combine($lang_parse[1], $lang_parse[4]);
                foreach ($langs as $lang => $val) {
                    if ($val === '') $langs[$lang] = 1;
                }
                arsort($langs, SORT_NUMERIC);
            }
        }
        foreach ($langs as $l) {
            if (in_array($l, $allowed)) {
                $_SESSION["lang"] = $l;
                return $l;
            }
        }
        $_SESSION["lang"] = DEFAULT_LANG;
        return DEFAULT_LANG;
    }

    public static function init($lang)
    {
        if (!in_array($lang, self::get_allowed_langs())) {
            $lang = self::detect_lang(DEFAULT_LANG);
        }
        if (file_exists(APP_DIR . DS . "Lang" . DS . $lang . ".ini")) {
            self::$lang = parse_ini_file(APP_DIR . DS . "Lang" . DS . $lang . ".ini");
        } else throw new \PKRS\Core\Exception\LangException("Lang file " . $lang . " not exists!");
    }

    public static function translate($params, $name, \Smarty &$smarty)
    {
        if (self::$loaded === false) {
            $config = \PKRS\Core\Service\ServiceContainer::get_instatce()->get_config();
            self::init($config->get("default_lang", "cs"));
            self::$loaded = true;
        }
        $translation = '';
        if (!is_null($name) && is_array(self::$lang) && array_key_exists($name, self::$lang)) {

            // get variables in translation text
            $translation = self::$lang[$name];
            preg_match_all('/##([^#]+)##/i', $translation, $vars, PREG_SET_ORDER);

            // replace with assigned smarty values
            foreach ($vars as $var) {
                $translation = str_replace($var[0], $smarty->getTemplateVars($var[1]), $translation);
            }

        } else {
            if (trim($name) != "")
                if (defined("DEV") && DEV) {
                    throw new \PKRS\Core\Exception\LangException("Lang key $name not exists!");
                } else {
                    return $name;
                }
        }

        return $translation;

    }

    public static function xlate($str)
    {
        if (self::$loaded === false) {
            $config = \PKRS\Core\Service\ServiceContainer::get_instatce()->get_config();
            self::init($config->get("default_lang", "cs"));
            self::$loaded = true;
        }
        $arg_count = func_num_args();

        if ($arg_count > 1) {
            $params = func_get_args();
            array_shift($params);
        } else {
            $params = array();
        }

        $out_str = isset(self::$lang[$str]) ? self::$lang[$str] : null;

        if (!$out_str) {
            throw new \PKRS\Core\Exception\LangException("Lang String Not Found in language " . $_SESSION["lang"] . " : $str");
        }
        return vsprintf($out_str, $params);
    }

}