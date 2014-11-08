<?php
/**************************************************************
 *
 * IPHPModule.php, created 5.11.14
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
namespace PKRS\Core\Modules;

interface IPHPModule
{

    /**
     * When module starts
     *
     * @param string $db_prefix = Module identifier (database_prefix)
     * @return void
     */
    public function start($db_prefix = "");

    /**
     * Helper for install module
     *
     * @param string $db_prefix = Module identifier (database_prefix)
     * @return bool
     */
    public function _install($db_prefix = "");

    /**
     * Helper for remove module
     *
     * @param string $db_prefix = Module identifier (database_prefix)
     * @return bool
     */
    public function _remove($db_prefix = "");

}