<?php

/**************************************************************
 *
 * Neon.php, created 8.11.14
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
class Neon
{

    private $neon;

    public function __construct()
    {
        if (!defined("__PKRS_NEON_LOADED")){
            require(dirname(__FILE__) . DS . "Neon_class.php");
            require(dirname(__FILE__) . DS . "Encoder.php");
            require(dirname(__FILE__) . DS . "Decoder.php");
            require(dirname(__FILE__) . DS . "Exception.php");
        }
        $this->neon = new \Neon\Neon_class();
    }

    public function decode($neon_file)
    {
        if (!file_exists($neon_file))
            throw new \PKRS\Core\Exception\FileException("Neon: file $neon_file not exists!");
        return $this->neon->decode(file_get_contents($neon_file));
    }

}