<?php
ini_set("allow_url_fopen", "true");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header('Access-Control-Max-Age: 86400');
if (getenv('REQUEST_METHOD') == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';

use Core\System\Config;
use Ratchet\App;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket\Type\Chat()
        )
    ),
    Config::config("webSocketPort")
);

$server->run();