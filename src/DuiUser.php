<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;

class DuiUser extends \yii\web\User
{
    public $tokenEntityClassName;
    
    /**
     * Finds user by access token
     *
     * @param string $token
     * @return static|null
     */
    public function findByAccessToken($token)
    {
        $entity = new $this->tokenEntityClassName;
        $model = $entity::find()
            ->where(['=', 'refresh_token', $token])
            ->andWhere(['>', 'refresh_expired_at', time()])
            ->one();

        return $model ? $model->user : null;
    }
}
