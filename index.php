<?php
require "Avdeev\Quantronix\Cubiscan.php";
use Avdeev\Quantronix\Cubiscan;

error_reporting(0);

$config = include("config.php");

$cubiscan = new Cubiscan($config["host"], $config["port"], 1);
$result = $cubiscan->measure();

header("Content-Type: application/json; charset=utf-8");
exit(json_encode($result));
