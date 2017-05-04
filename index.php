<?php
require_once 'Avdeev\Quantronix\Cubiscan.php';

use Avdeev\Quantronix\Cubiscan;

error_reporting(0);

$ip = "10.0.37.3";
$port = 1050;

$cubiscan = new Cubiscan($ip, $port, 2);
$result = $cubiscan->measure();

header("Content-Type: application/json; charset=utf-8");
exit(json_encode($result));
