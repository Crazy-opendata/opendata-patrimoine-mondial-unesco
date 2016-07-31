<?php

namespace CrazyOpendata;

require_once __DIR__."/vendor/autoload.php";

use CrazyOpendata\Core\Config;
use CrazyOpendata\Core\Export;


$mysql_user  = $argv[1];
$mysql_pass  = $argv[2];
$mysql_db    = "data";
$mysql_host  = "localhost";
$mysql_table = "patrimoine";

$config = new Config(
    $mysql_user,
    $mysql_pass,
    $mysql_db,
    $mysql_host,
    $mysql_table
);

Export::exportAll($config, "patrimoine");
