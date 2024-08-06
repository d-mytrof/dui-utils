<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\helpers\Json;

class DuiHttpHybridBearerAuth extends BaseHttpBearerAuth
{
    public $entityClassName = 'models\SystemClient';
    public $identityClass;
    public $tokenEntityClassName;
    public $defaultClientName;
    
    public const JWT_METHOD = 'HS256';
    public const CLIENT_ADMIN = 'admin';
    
    /**
     * @param mixed $user
     * @return void
     */
    private function setParams(mixed $user): void
    {
        if (!empty($this->identityClass)) {
            $user->identityClass = $this->identityClass;
        }
        if (!empty($this->tokenEntityClassName)) {
            $user->tokenEntityClassName = $this->tokenEntityClassName;
        }
    }
    
    /**
     * @param mixed $request
     * @return string|null
     */
    private function getGwt(mixed $request): ?string
    {
        $jwt = null;
        $authHeader = $request->getHeaders()->get($this->header);
        $pattern = '/^Bearer\s+(.*?)$/';
        if ($authHeader !== null) {
            if ($pattern !== null) {
                if (preg_match($pattern, $authHeader, $matches)) {
                    $jwt = $matches[1];
                } else {
                    return null;
                }
            }
        }
        
        return $jwt;
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $this->setParams($user);
        $jwt = $this->getGwt($request);

        if (!$jwt) {
            return null;
        }

        $tks = explode('.', $jwt);
        if (count($tks) !== 3) {
            return null;
        }
        $bodyb64 = $tks[1];
        $json = Json::decode(base64_decode($bodyb64), false);
        
        $entity = new $this->entityClassName;
        $fields = [
            'client_id' => Yii::$app->appSecurity->getEncrypted($json->client_id),
            'status' => $entity::STATUS_ACTIVE,
        ];
        if ($this->defaultClientName) {
            $fields = [
                'name' => $this->defaultClientName
            ];
        }
        $model = $entity::findOne($fields);
        if (!$model) {
            return null;
        }

        try {
            $decoded = JWT::decode($jwt, new Key($model->private_key, self::JWT_METHOD));
        } catch (\Exception $e) {
            return null;
        }

        if ($decoded->client_id !== $model->client_id || empty($decoded->user_token)) {
            return null;
        }

        $identity = $user->findByAccessToken($decoded->user_token);

        if ($identity === null) {
            $this->challenge($response);
            $this->handleFailure($response);
        }
        
        Yii::$app->user->setIdentity($identity);

        return $identity;
    }
}
