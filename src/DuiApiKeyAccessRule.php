<?php
/**
 * @copyright Copyright © 2025 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DuiApiKeyAccessRule extends \yii\filters\AccessRule
{
    public string $entityClassName = 'models\ApiKeyClient';
    
    public const JWT_METHOD = 'RS256';
    
    private array $userRoles = [];
    
    /**
     * @param mixed $request
     * @return string|null
     */
    private function getGwt(mixed $request): ?string
    {
        $jwtToken = null;
        $authHeader = $request->getHeaders()->get('Authorization');
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
     * @return bool
     */
    private function getIsGuest(): bool
    {
        $jwtToken = $this->getGwt(Yii::$app->request);

        if (!$jwtToken) {
            return true;
        }
        
        $entity = new $this->entityClassName;
        $auth = $entity::findOne(['name' => $entity::CLIENT_AUTH]);
        if (!$auth) {
            return true;
        }

        try {
            $decoded = JWT::decode($jwtToken, new Key($auth->public_key, self::JWT_METHOD));
        } catch (\Exception $e) {
            return true;
        }

        if ($decoded->client_name === $entity::CLIENT_AUTH) {
            $this->userRoles = isset($decoded->role) ? array_filter(explode(',', $decoded->role)) : [];
            return false;
        }

        return true;
    }

    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($this->getIsGuest() || !$this->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$this->getIsGuest()) {
                    return true;
                }
                //check if the user is logged in and the roles match
            } elseif (!$this->getIsGuest() && in_array($role, $this->userRoles)) {
                return true;
            }
        }

        return false;
    }
}
