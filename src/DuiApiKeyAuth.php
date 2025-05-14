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
use yii\web\UnauthorizedHttpException;
use Exception;
use dmytrof\DuiBucketSDK\Helpers\DuiEncryption;

class DuiApiKeyAuth extends BaseHttpBearerAuth
{
    public const JWT_METHOD = 'RS256';

    public string $entityClassName = 'models\ApiKeyClient';

    public array $apiKeyClients = [];

    public bool $validateToken = false;

    private function getApiKey(mixed $request): mixed
    {
        $authHeader = $request->getHeaders()->get('x-api-key');

        if (!$authHeader) {
            $authHeader = $request->cookies->get('x-api-key');

            if ($authHeader) {
                try {
                    $authHeader = (new DuiEncryption)->decrypt($authHeader);
                } catch (Exception $e) {
                    return null;
                }
            }
        }

        if(!$authHeader) {
            throw new UnauthorizedHttpException(Yii::t('basic', 'API_INVALID_KEY'));
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
            throw new UnauthorizedHttpException(Yii::t('basic', 'API_INVALID_KEY'));
        }

        if (!empty($this->apiKeyClients) && !in_array($model->name, $this->apiKeyClients)) {
            throw new UnauthorizedHttpException(Yii::t('basic', 'API_INVALID_KEY'));
        }

        return $model;
    }

    /**
     * @param mixed $request
     * @return string|null
     */
    private function getGwt(mixed $request): ?string
    {
        $jwtToken = null;
        $authHeader = $request->getHeaders()->get($this->header);
        $pattern = '/^Bearer\s+(.*?)$/';
        if ($authHeader !== null) {
            if ($pattern !== null) {
                if (preg_match($pattern, $authHeader, $matches)) {
                    $jwtToken = $matches[1];
                } else {
                    return null;
                }
            }
        }

        return $jwtToken;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $model = $this->getApiKey($request);

        if (!$model) {
            return null;
        }

        $entity = new $this->entityClassName;
        $auth = $entity::findOne(['name' => $entity::CLIENT_AUTH]);
        if (!$auth) {
            return null;
        }

        if ($this->validateToken) {
            $jwtToken = $this->getGwt($request);

            if (!$jwtToken) {
                return null;
            }

            if (!$auth->public_key) {
                throw new UnauthorizedHttpException(Yii::t('basic', 'AUTH_KEY_REQUIRED'));
            }

            try {
                $decoded = JWT::decode($jwtToken, new Key($auth->public_key, self::JWT_METHOD));
            } catch (\Exception $e) {
                return null;
            }

            if ($decoded->client_name !== $entity::CLIENT_AUTH) {
                return null;
            }
        }

        $model->api_key = null;
        $model->new_api_key = null;
        $model->public_key = null;
        $model->private_key = null;

        return $model;
    }
}
