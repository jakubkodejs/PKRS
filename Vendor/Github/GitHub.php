<?php
/**************************************************************
 *
 * GitHub.php, created 9.11.14
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

class GitHub{

    private $github;

    public function __construct($login, $pass){
        require_once(__DIR__ . '/client/GitHubClient.php');
        $client = new GitHubClient();
        $client->setCredentials($login, $pass);
        $client->setPage();
        $client->setPageSize(2);
        $commits = $client->repos->commits->listCommitsOnRepository($login, "PKRS");
        var_dump($commits);
        exit;
    }

}