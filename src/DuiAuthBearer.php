<?php
/**
 * @copyright Copyright © 2025 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use components\Exception;
use Yii;
use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use dmytrof\DuiBucketSDK\Helpers\DuiEncryption;
use yii\web\ForbiddenHttpException;

class DuiAuthBearer extends BaseHttpBearerAuth
{
    public const JWT_METHOD = 'RS256';

    public string $entityClassName = 'models\ApiKeyClient';
    public ?string $identityClass;
    public array $userGroups = [];

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
    }

    /**
     * @param mixed $request
     * @return mixed
     */
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
            throw new ForbiddenHttpException(Yii::t('basic', 'API_INVALID_KEY'));
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
            throw new ForbiddenHttpException(Yii::t('basic', 'API_INVALID_KEY'));
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

        $jwt = $this->getGwt($request);

        if (!$jwt) {
            return null;
        }

        $entity = new $this->entityClassName;
        $auth = $entity::findOne(['name' => $entity::CLIENT_AUTH]);
        if (!$auth) {
            throw new ForbiddenHttpException(Yii::t('basic', 'API_INVALID_KEY'));
        }

        try {
            $decoded = JWT::decode($jwt, new Key($auth->public_key, self::JWT_METHOD));
        } catch (\Exception $e) {
            return null;
        }

        if ($decoded->client_name !== $entity::CLIENT_AUTH || empty($this->jwtToken) || empty($decoded->uid) || empty($decoded->groups)) {
            return null;
        }

        $common = array_intersect($decoded->groups, $this->userGroups);
        if (empty($common)) {
            return null;
        }

        $identity = new $user->identityClass;
        $identity->uid = $decoded->uid;
        $identity->groups = $decoded->groups;
        if ($identity === null) {
            $this->challenge($response);
            $this->handleFailure($response);
        }
;
        Yii::$app->user->setIdentity($identity);

        return $identity;
    }
}
