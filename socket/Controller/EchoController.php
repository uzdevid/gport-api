<?php

namespace socket\Controller;

use UzDevid\WebSocket\Controller;
use UzDevid\WebSocket\Server\Dto\Client;

class EchoController extends Controller {
    /**
     * @param Client $client
     * @param array $payload
     * @return void
     */
    public function actionEcho(Client $client, array $payload): void {
        $client->user->send('echo:echo', ['currentTime' => time()]);
    }
}