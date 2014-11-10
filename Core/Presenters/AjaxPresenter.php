<?php
/**************************************************************
 *
 * AjaxPresenter.php, created 9.11.14
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
namespace PKRS\Core\Presenters;

abstract class AjaxPresenter extends BasePresenter{

    var $json = array();

    function need_login()
    {

    }

    /**
     * Not implemeted, but inheritance need this method
     */
    final function set_theme(){}

    /**
     * Exit before starting display
     */
    function before_display(){
        ob_clean();
        echo json_encode($this->json);
        exit;
    }
}