<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;

class DuiHttpSystemBearerAuth extends HttpBearerAuth
{
    public $entityClassName;
    
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $entity = new $this->entityClassName;
        Yii::$app->client->clientPublicKey = $request->getHeaders()->get('x-api-system-client');
        $token = $request->getHeaders()->get('x-api-system-client-token');

        if (Yii::$app->client->clientPublicKey) {
            $identity = $entity::findOne([
                'client_id' => Yii::$app->appSecurity->getEncrypted(Yii::$app->client->clientPublicKey),
                'client_type' => $entity::TYPE_SYSTEM,
                'status' => $entity::STATUS_ACTIVE,
            ]);
            if (!$identity) {
                return null;
            }
            Yii::$app->client->clientPrivateKey = $identity->private_key;
        } else {
            return null;
        }

        if (!$token || !Yii::$app->client->tokenIsValid($token, $identity)) {
            throw new ForbiddenHttpException('Undefined application client 123');
        }

        if ($identity === null) {
            $this->challenge($response);
            $this->handleFailure($response);
            return null;
        }

        Yii::$app->user->setIdentity($identity);

        return $identity;
    }
}
