<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace components;

use Yii;
use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\helpers\Json;

class DuiApiKeyBearerAuth extends BaseHttpBearerAuth
{
    public const JWT_METHOD = 'HS256';
    
    public $entityClassName = 'models\ApiKeyClient';
    public $identityClass;
    public $tokenEntityClassName;
    
    private $jwtToken = null;
    
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

    private function getApiKey(mixed $request): mixed
    {
        $authHeader = $request->getHeaders()->get('X-API-Key');
        
        if(!$authHeader) {
            return null;
        }
        
        $entity = new $this->entityClassName;
        $model = $entity::find()
        ->where([
            'status' => $entity::STATUS_ACTIVE,
        ])
        ->andWhere([
            'or',
            ['api_key' => $authHeader],
            ['new_api_key' => $authHeader]
        ])
        ->one();
        
        if (!$model) {
            return null;
        }

        return $model;
    }
    
    /**
     * @param mixed $request
     * @return string|null
     */
    private function getGwt(mixed $request): ?string
    {
        $authHeader = $request->getHeaders()->get($this->header);
        $pattern = '/^Bearer\s+(.*?)$/';
        if ($authHeader !== null) {
            if ($pattern !== null) {
                if (preg_match($pattern, $authHeader, $matches)) {
                    $this->jwtToken = $matches[1];
                } else {
                    return null;
                }
            }
        }
        
        return $this->jwtToken;
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $this->setParams($user);
        $model = $this->getApiKey($request);

        if (!$model) {
            return null;
        }
        
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
        $auth = $entity::findOne(['name' => $entity::CLIENT_AUTH]);

        try {
            $decoded = JWT::decode($jwt, new Key($auth->private_key, self::JWT_METHOD));
        } catch (\Exception $e) {
            return null;
        }

        if ($decoded->client_name !== $entity::CLIENT_AUTH || empty($this->jwtToken)) {
            return null;
        }

        $identity = $user->findByAccessToken($this->jwtToken);

        if ($identity === null) {
            $this->challenge($response);
            $this->handleFailure($response);
        }
        
        Yii::$app->user->setIdentity($identity);

        return $identity;
    }
}
