<?php
include(dirname(__FILE__).DIRECTORY_SEPARATOR."PKRS".DIRECTORY_SEPARATOR."PKRS.php");

$pkrs = new PKRS\PKRS(dirname(__FILE__) . "/config.neon");

$router = $pkrs->service()->get_router();
$router->setBasePath($pkrs->config()->get("base_path"));

// front
$router->map("GET", "", array("ns" => "Front", "c" => "Main", "a" => "homepage"));

// Start the application
$pkrs->run();