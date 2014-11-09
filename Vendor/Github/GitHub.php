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
class GitHub
{

    private $github;
    private $log = array();
    private $cache = array();

    public function __construct($login, $pass)
    {
        $this->cache = $this->get_cache();
        require_once(__DIR__ . '/client/GitHubClient.php');
        $client = new GitHubClient();
        $this->github = $client;
        $this->github->setCredentials("pitrrs","7pk3Pq55s6p3+");
        $repo = $this->github->repos->get("pitrrs","PKRS");
        $last_pust = strtotime($repo->pushed_at);
        if ($last_pust > $this->cache_get_last()){
            $this->log("Need update");
            $commits = $this->github->repos->commits->listCommitsOnRepository("pitrrs","PKRS",null,null,null,date("c",$this->cache_get_last()));
            $this->log(count($commits)." new commits");
            foreach (array_reverse($commits) as $commit){
                $cache = array(
                    "sha"=>$commit->sha,
                    "message"=>$commit->commit->message,
                    "files"=>array(),
                    "date"=>strtotime($commit->commit->committer->date)
                );
                foreach($this->get_files_in_commit($commit->sha) as $file){
                    $cachef = array(
                        "file"=>$file->filename,
                        "file_path"=>APP_DIR . "/PKRS/" . $file->filename,
                        "status"=>$file->status,
                        "raw"=>$file->raw_url,
                        "changes"=>$file->patch
                    );
                    if ($cachef["status"] == "removed"){
                        if (file_exists($cachef["file_path"]))
                        {
                            $this->log("Deleting ".$cachef["file_path"]);
                            unlink($cachef["file_path"]);
                        }
                    } else {
                        $this->log("Updating ".$cachef["file_path"]);
                        $h = fopen($cachef["file_path"],"w+");
                        fwrite($h, file_get_contents($cachef["raw"]));
                        fclose($h);
                    }
                    $cache["files"][] = $cachef;
                }
                $this->cache["pushed"] = $cache["date"];
                $this->cache["commits"][$commit->sha] = $cache;
                $this->store_cache();
                break; // only one commit
            }
            var_dump($this->log);
            exit;
            //var_dump($last_pust);;
            //exit;
            //$this->get_files_in_commit("0179fff5bcbf56b9c46310f3568d51d9806ce891");
        } else $this->log("Actual repository");


        echo implode("\n<br>", $this->log);
        exit;
    }

    private function get_files_in_commit($sha)
    {
        return $this->github->repos->commits->getSingleCommit("pitrrs", "PKRS", $sha)->files;
       /* foreach ($commit->files as $file) {
            $filename = APP_DIR . "/PKRS/" . $file->filename;
            if ($file->status == "removed") {
                if (file_exists($filename)) {
                    $this->log("TODO: Deleted and now exists - " . $file->filename);
                } else $this->log("OK: Deleted and now not exists - " . $file->filename);
            } else {
                $this->log("TODO: Update file - " . $file->filename);
            }
       //     var_dump($file->status . " - " . $file->raw_url);
        } */
    }

    private function log($message)
    {
        $h = fopen(dirname(__FILE__).DS."log.txt","a");
        fwrite($h, $message.PHP_EOL);
        fclose($h);
        $this->log[] = $message;
    }

    private function cache_get_last(){
        if (!isset($this->cache["pushed"])) {
            $this->cache["pushed"] = filemtime(ROOT_DIR."index.php");
            $this->store_cache();
        }
        return intval($this->cache["pushed"]);
    }

    private function store_cache(){
        $h = fopen(dirname(__FILE__).DS."cache.json","w+");
        fwrite($h, json_encode($this->cache,JSON_PRETTY_PRINT));
        fclose($h);
    }

    private function get_cache(){
        $cache = array("commits"=>array());
        if (file_exists(dirname(__FILE__).DS."cache.json")){
            $cache = (array)json_decode(file_get_contents(dirname(__FILE__).DS."cache.json"), true);
        } else {
            $h = fopen(dirname(__FILE__).DS."cache.json","w+");
            fwrite($h, json_encode((array)$cache,JSON_PRETTY_PRINT ));
            fclose($h);
        }
        return $cache;
    }

}