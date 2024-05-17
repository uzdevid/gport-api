<?php

use socket\Bootstrap\EventHandler;
use UzDevid\WebSocket\Server\WebSocketServer;

$params = array_merge(
    require __DIR__ . '/../../common/Config/params.php',
    require __DIR__ . '/../../common/Config/Local/params.php',
    require __DIR__ . '/params.php',
);

return [
    'id' => 'gport-socket',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'socket\\Controller',
    'bootstrap' => [EventHandler::class],
    'webSocketServer' => [
        'class' => WebSocketServer::class,
        'host' => 'localhost',
        'port' => 8080,
        'count' => 1
    ],
    'components' => require __DIR__ . '/Component/main.php',
    'params' => $params,
];
