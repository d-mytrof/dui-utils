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

class DuiHttpHybridClientBearerAuth extends BaseHttpBearerAuth
{
    public $entityClassName = 'models\SystemClient';
    
    public const JWT_METHOD = 'HS256';
    
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
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
        $model = $entity::findOne([
            'client_id' => Yii::$app->appSecurity->getEncrypted($json->client_id),
            'status' => $entity::STATUS_ACTIVE,
        ]);
        if (!$model) {
            return null;
        }

        try {
            $decoded = JWT::decode($jwt, new Key($model->private_key, self::JWT_METHOD));
        } catch (\Exception $e) {
            return null;
        }

        if ($decoded->client_id !== $model->client_id) {
            return null;
        }
        
        return $model;
    }
}
