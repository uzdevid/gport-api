<?php

namespace proxy\Controller;

use common\Exception\Message\NotFound;
use common\Exception\NotFoundHttpException;
use common\Model\Sharing;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class AppController extends Controller {
    /**
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionIndex(): void {
        $sharing = Sharing::find()->where(['remote_address' => $_SERVER['HTTP_HOST'], 'is_active' => true])->orderBy(['created_time' => SORT_DESC])->one();

        if (is_null($sharing)) {
            throw new NotFoundHttpException(NotFound::PROXY_NOT_FOUND);
        }

        $requestId = Uuid::uuid4()->toString();

        $client = Yii::$app->webSocketClient;

//        Yii::$app->queue->push(new CallLocalAddress($client, [
//            'connectionId' => $sharing->connection_id,
//            'requestId' => $requestId,
//            'address' => $sharing->local_address . $_SERVER['REQUEST_URI'],
//        ]));

        $client->send('local-client:call-address', [
            'connectionId' => $sharing->connection_id,
            'requestId' => $requestId,
            'address' => $sharing->local_address . $_SERVER['REQUEST_URI'],
        ]);

        $client->client->setTimeout(40);

        $timeout = 40;
        $startedAt = time();

        while (true) {
            if (!$client->client->isConnected()) {
                throw new BadRequestHttpException('Not connected');
            }

            $json = Json::decode($client->client->receive());

            if ($json['payload']['requestId'] !== $requestId) {
                continue;
            }

            if (time() > ($startedAt + $timeout)) {
                throw new NotFoundHttpException(NotFound::RESOURCE_NOT_FOUND);
            }

            break;
        }

        $contentType = $json['payload']['content']['type'];

        $content = gzdecode(base64_decode($json['payload']['content']['base64']));

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Description', $contentType);
        Yii::$app->response->headers->set('Content-Type', $contentType);
        Yii::$app->response->content = $content;
        Yii::$app->response->send();
    }
}