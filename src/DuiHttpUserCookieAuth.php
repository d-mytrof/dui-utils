<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;

class DuiHttpUserCookieAuth extends \yii\filters\auth\AuthMethod
{
    public $cookie = 'sid';
    public $entityClassName;

    public function authenticate($user, $request, $response)
    {
        $cookies = Yii::$app->request->cookies;
        $token = isset($cookies[$this->cookie]) ? $cookies[$this->cookie] : null;
        if ($token) {
            $entity = new $this->entityClassName;
            $identity = $entity::findByAccessToken($token);
        }

        if ($identity !== null) {
            Yii::$app->user->setIdentity($identity);
            return $identity;
        }

        if ($token !== null) {
            $this->handleFailure($response);
        }

        return null;
    }
}
