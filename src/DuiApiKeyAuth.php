<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;

class DuiApiKeyAuth extends BaseHttpBearerAuth
{
    public string $entityClassName = 'models\ApiKeyClient';
    
    public array $apiKeyClients = [];

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
        
        if (!empty($this->apiKeyClients) && !in_array($model->name, $this->apiKeyClients)) {
            return null;
        }

        return $model;
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

        $model->api_key = null;
        $model->new_api_key = null;
        $model->public_key = null;
        $model->private_key = null;

        return $model;
    }
}
