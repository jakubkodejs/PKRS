<?php
/**************************************************************
 *
 * Main.php, created 9.11.14
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
 **************************************************************/
namespace Presenters\Front;

class Main extends \PKRS\Core\Presenters\BasePresenter{

    function homepage($params){
        $this->template = "homepage.tpl";
        $this->smarty->assign("test", "Hello World! :)");
    }

    function need_login()
    {
        $this->need_login = false;
    }

    function set_theme()
    {
        $this->theme = "Example";
    }
}
