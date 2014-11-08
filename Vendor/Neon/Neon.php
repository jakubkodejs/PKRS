<?php

/**************************************************************
 *
 * Neon.php, created 8.11.14
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
class Neon
{

    private $neon;

    public function __construct()
    {
        include(dirname(__FILE__) . DS . "Neon_class.php");
        include(dirname(__FILE__) . DS . "Encoder.php");
        include(dirname(__FILE__) . DS . "Decoder.php");
        $this->neon = new \Neon\Neon_class();
    }

    public function decode($neon_file)
    {
        if (!file_exists($neon_file))
            throw new \PKRS\Core\Exception\FileException("Neon: file $neon_file not exists!");
        return $this->neon->decode(file_get_contents($neon_file));
    }

}