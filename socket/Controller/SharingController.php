<?php

namespace socket\Controller;

use common\Model\Sharing;
use Ramsey\Uuid\Uuid;
use socket\Message\SharingResponse;
use socket\Service\Dns;
use UzDevid\WebSocket\Controller;
use UzDevid\WebSocket\Server\Dto\Client;
use Workerman\Timer;
use Yii;
use yii\base\Exception;

class SharingController extends Controller {
    /**
     * @throws Exception
     */
    public function actionShare(Client $client, array $payload): void {
        $sharing = new Sharing();

        if (empty($payload['domain'])) {
            $sharing->remote_address = sprintf('%s.gport.uz', $sharing->key);
        } else if (!str_ends_with("gport.uz", $payload['domain']) && Dns::checkIp($payload['domain'], '85.92.110.145')) {
            $domain = $payload['domain'];

            if (parse_url($domain, PHP_URL_SCHEME) === null) {
                $domain = sprintf("http://%s", $domain);
            }

            $sharing->remote_address = $domain;
        }

        $sharing->key = strtolower(Yii::$app->security->generateRandomString(4));
        $sharing->user_id = Uuid::uuid4()->toString();
        $sharing->connection_id = $client->id;
        $sharing->local_address = $payload['localAddress'];
        $sharing->active = 0;
        $sharing->is_active = true;

        $sharing->save();

        $message = new SharingResponse($sharing);

        $client->user->send('SharingResponse', $message);

        Timer::add(5, static function () use ($client) {
            $client->user->send("LocalClient:is-online", [date('Y-m-d H:i:s')]);
        });
    }
}