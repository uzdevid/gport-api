<?php

namespace socket\Controller;

use common\Model\Sharing;
use Ramsey\Uuid\Uuid;
use socket\Message\PrintMessage;
use socket\Message\SharingResponse;
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
        } else if (str_ends_with($payload['domain'], ".gport.uz")) {
            $remoteAddress = parse_url(str_starts_with($payload['domain'], "http") ? $payload['domain'] : sprintf("http://%s", $payload['domain']), PHP_URL_HOST);
        } else {
            $client->user->send(PrintMessage::methodName(), new PrintMessage(sprintf("[31mInvalid domain: %s[0m", $payload['domain'])));
            return;
        }

        $isUsed = Sharing::find()->where(['remote_address' => $remoteAddress, 'is_active' => true])->exists();

        if ($isUsed) {
            $client->user->send(PrintMessage::methodName(), new PrintMessage(sprintf("[31mYou cannot use this domain at this time: %s[0m", $payload['domain'])));
            return;
        }

        $localAddress = $payload['localAddress'];

        if (!str_starts_with($payload['localAddress'], 'http://') && !str_starts_with($payload['localAddress'], 'https://')) {
            $localAddress = sprintf("http://%s", $payload['localAddress']);
        }

        $sharing->remote_address = $remoteAddress;
        $sharing->user_id = Uuid::uuid4()->toString();
        $sharing->connection_id = $client->id;
        $sharing->local_address = $localAddress;
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