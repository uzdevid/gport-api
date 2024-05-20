<?php

namespace socket\Controller;

use common\Model\Sharing;
use Ramsey\Uuid\Uuid;
use socket\Message\PrintMessage;
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

        $sharing->key = strtolower(Yii::$app->security->generateRandomString(4));

        if (empty($payload['domain'])) {
            $remoteAddress = sprintf('%s.gport.uz', $sharing->key);
        } else if (str_ends_with("gport.uz", $payload['domain'])) {
            $remoteAddress = parse_url($payload['domain'], PHP_URL_HOST);
        } else if (Dns::checkIp($payload['domain'], '185.154.194.150')) {
            $remoteAddress = parse_url(str_starts_with("http", $payload['domain']) ? $payload['domain'] : sprintf("http://%s", $payload['domain']), PHP_URL_HOST);
        } else {
            $client->user->send(PrintMessage::methodName(), new PrintMessage(sprintf("[31mInvalid domain: %s[0m", $payload['domain'])));
            return;
        }

        $sharing->remote_address = $remoteAddress;
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