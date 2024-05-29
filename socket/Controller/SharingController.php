<?php

namespace socket\Controller;

use common\Model\Sharing;
use socket\Message\PrintMessage;
use socket\Message\SharingResponse;
use UzDevid\WebSocket\Controller;
use UzDevid\WebSocket\Server\Dto\Client;
use Workerman\Timer;
use Yii;
use yii\base\Exception;
use yii\helpers\Console;
use yii\helpers\Json;

class SharingController extends Controller {
    /**
     * @throws Exception
     */
    public function actionShare(Client $client, array $payload): void {
        $sharing = new Sharing();

        $key = strtolower(Yii::$app->security->generateRandomString(4));

        if (empty($payload['domain'])) {
            $remoteAddress = sprintf('%s.gport.uz', $key);
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

        if (!str_starts_with($localAddress, 'http://') && !str_starts_with($localAddress, 'https://')) {
            $localAddress = sprintf("http://%s", $localAddress);
        }

        $sharing->user_id = "c5a58a61-584e-46df-9844-1460a5c1a9ff";
        $sharing->access_id = "6f8c215b-4e79-4828-9dc6-6af18bb9795e";
        $sharing->client_id = $client->id;
        $sharing->protocol = "http";

        $sharing->remote = $remoteAddress;
        $sharing->local = $localAddress;
        $sharing->active = 0;
        $sharing->is_active = true;

        $sharing->save();

        Console::stdout(Json::encode($sharing->errors));
        Console::stdout("\r\n------------------------\r\n");

        $message = new SharingResponse($sharing);

        $client->user->send('SharingResponse', $message);

        Timer::add(5, static function () use ($client) {
            $client->user->send("LocalClient:is-online", [date('Y-m-d H:i:s')]);
        });
    }
}