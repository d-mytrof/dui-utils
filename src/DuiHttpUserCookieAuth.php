<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace components;

use Yii;
use models\User;

class DuiHttpUserCookieAuth extends \yii\filters\auth\AuthMethod
{
    public $cookie = 'sid';

    public function authenticate($user, $request, $response)
    {
        $token = isset($_COOKIE[$this->cookie]) ? $_COOKIE[$this->cookie] : null;
        $identity = User::findByAccessToken($token);

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
