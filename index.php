<?php
ini_set("allow_url_fopen", "true");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/vendor/autoload.php';

use Core\System\Config;
use \Whoops\Run;

$whoops = new Run;

if(Config::config("environment") == "dev"){
    ob_start();
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
}else{
    ini_set('display_errors', 'Off');
    error_reporting(0);
    $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
}

$whoops->register();

require_once __DIR__ . '/routes.php';