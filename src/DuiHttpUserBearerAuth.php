<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;

class DuiHttpUserBearerAuth extends BaseHttpBearerAuth
{
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
            }

            $identity = $user->findByAccessToken($authHeader);

            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            Yii::$app->user->setIdentity($identity);

            return $identity;
        }

        return null;
    }
}
