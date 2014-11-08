<?php
/**************************************************************
 *
 * Smarty.php, created 4.11.14
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
namespace PKRS\Core\View;

class Smarty extends \PKRS\Core\Service\Service
{

    var $smarty;

    public function __construct(\Smarty $smarty)
    {
        $smarty->debugging = false;
        $smarty->caching = false;
        $smarty->force_compile = true;
        if (!is_dir(ROOT_DIR . "cache")) @mkdir(ROOT_DIR . "cache");
        $smarty->setCompileDir(ROOT_DIR . "cache");
        $smarty->setCacheDir(ROOT_DIR . "cache");
        $smarty->registerPlugin('block', 't', array("\\PKRS\\Core\\Lang\\Lang", "translate"), false);
        $this->smarty = $smarty;
    }

    public function smarty()
    {
        return $this->smarty;
    }

    public function new_instace()
    {
        $smarty = new \Smarty();
        $smarty->debugging = false;
        $smarty->caching = false;
        $smarty->force_compile = true;
        if (!is_dir(ROOT_DIR . "cache")) @mkdir(ROOT_DIR . "cache");
        $smarty->setCompileDir(ROOT_DIR . "cache");
        $smarty->setCacheDir(ROOT_DIR . "cache");
        $smarty->registerPlugin('block', 't', array("\\PKRS\\Core\\Lang\\Lang", "translate"), false);
        return $smarty;
    }

}