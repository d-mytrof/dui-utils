<?php

/**
 * Copyright © Dmytro Mytrofanov, 2025
 * Email: dmitrof.development@gmail.com
 */

namespace dmytrof\utils;

use Yii;
use yii\base\ActionFilter;
use yii\web\TooManyRequestsHttpException;

class DuiRateLimiter extends ActionFilter
{
    public array $actions = [];
    public int $maxRequests = 1;
    public int $interval = 60;

    public function beforeAction($action)
    {
        if (!array_key_exists($action->id, $this->actions)) {
            return parent::beforeAction($action);
        }

        $userIdentifierKeyFromBody = $this->actions[$action->id];
        
        $db = Yii::$app->db;
        $key = $this->getRateLimitKey($userIdentifierKeyFromBody);
        $limitTime = date('Y-m-d H:i:s', time() - $this->interval);

        $db->createCommand("DELETE FROM rate_limits WHERE created_at < :limitTime", [':limitTime' => $limitTime])->execute();

        $requestCount = $db->createCommand("SELECT COUNT(*) FROM rate_limits WHERE user_identifier = :key AND action = :action", [
            ':key' => $key,
            ':action' => $action->id
        ])->queryScalar();

        if ($requestCount >= $this->maxRequests) {
            throw new TooManyRequestsHttpException(Yii::t('basic', 'RATE_LIMIT'));
        }

        $db->createCommand()->insert('rate_limits', [
            'user_identifier' => $key,
            'action' => $action->id,
        ])->execute();

        return parent::beforeAction($action);
    }

    /**
     * @param string|null $userIdentifierKeyFromBody
     * @return string|null
     */
    private function getRateLimitKey(?string $userIdentifierKeyFromBody): ?string
    {
        if ($userIdentifierKeyFromBody && Yii::$app->request->getBodyParam($userIdentifierKeyFromBody)) {
            return Yii::$app->request->getBodyParam($userIdentifierKeyFromBody);
        }
        
        return Yii::$app->user->id ?? Yii::$app->request->userIP;
    }
}
